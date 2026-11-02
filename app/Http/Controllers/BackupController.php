<?php

namespace App\Http\Controllers;

use App\Models\AssetItemImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        return view('backups.index');
    }

    public function download(): BinaryFileResponse
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Fitur backup membutuhkan ekstensi PHP zip. Aktifkan ekstensi tersebut lalu restart web server.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'veloassets-backup-');
        if ($zipPath === false) {
            throw new RuntimeException('Tidak dapat menyiapkan file sementara untuk backup.');
        }
        $sqlPath = tempnam(sys_get_temp_dir(), 'veloassets-database-');
        if ($sqlPath === false) {
            unlink($zipPath);
            throw new RuntimeException('Tidak dapat menyiapkan file sementara untuk backup.');
        }

        $handedToResponse = false;
        $archive = null;
        $archiveIsOpen = false;

        try {
            $this->writeDatabaseDump($sqlPath);

            $archive = new ZipArchive;
            $opened = $archive->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($opened !== true) {
                throw new RuntimeException('File arsip backup tidak dapat dibuat (kode '.$opened.').');
            }
            $archiveIsOpen = true;

            if (! $archive->addFile($sqlPath, 'database.sql')) {
                $archive->close();
                $archiveIsOpen = false;
                throw new RuntimeException('File dump database gagal ditambahkan ke arsip backup.');
            }

            $imageCount = $this->addAssetImages($archive);
            if (! $archive->addFromString('README.txt', implode("\r\n", [
                'VeloAssets database and asset image backup',
                'Created at: '.now()->toDateTimeString(),
                '',
                'Contents:',
                '- database.sql: SQL dump of application database tables and rows.',
                '- images/: private uploaded asset images, retaining their storage paths.',
                '',
                'Restore the SQL dump into a compatible database, then upload files under images/',
                'to the configured private asset-image disk while preserving their relative paths.',
                'The archive includes database user records and must be kept secure.',
            ]))) {
                $archive->close();
                $archiveIsOpen = false;
                throw new RuntimeException('Petunjuk pemulihan gagal ditambahkan ke arsip backup.');
            }

            if (! $archive->close()) {
                throw new RuntimeException('Arsip backup tidak dapat diselesaikan.');
            }
            $archiveIsOpen = false;

            $filename = 'veloassets-backup-'.now()->format('Ymd-His').'-'.$imageCount.'-images.zip';
            $response = response()->download($zipPath, $filename, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
            $handedToResponse = true;

            return $response;
        } finally {
            if ($archiveIsOpen && $archive instanceof ZipArchive && ! $archive->close()) {
                report(new RuntimeException('Arsip sementara gagal ditutup saat proses backup dihentikan.'));
            }
            if (is_file($sqlPath)) {
                unlink($sqlPath);
            }
            if (! $handedToResponse && is_file($zipPath)) {
                unlink($zipPath);
            }
        }
    }

    private function writeDatabaseDump(string $path): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        if ($driver === 'pgsql') {
            $this->writePostgresDatabaseDump($path, $connection->getConfig());

            return;
        }
        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            throw new RuntimeException('Backup saat ini mendukung PostgreSQL, MySQL, MariaDB, dan SQLite. Driver aktif: '.$driver.'.');
        }

        $output = fopen($path, 'wb');
        if ($output === false) {
            throw new RuntimeException('File dump database tidak dapat dibuka.');
        }

        $pdo = $connection->getPdo();

        try {
            $pdo->beginTransaction();
            $this->write($output, "-- VeloAssets database backup\n");
            $this->write($output, '-- Created at '.now()->toDateTimeString()."\n\n");
            $tables = $driver === 'sqlite'
                ? $this->sqliteTables($pdo)
                : $this->mysqlTables($pdo);

            if ($driver !== 'sqlite') {
                $this->write($output, "SET FOREIGN_KEY_CHECKS=0;\n\n");
            } else {
                $this->write($output, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n\n");
            }

            foreach ($tables as [$table, $createSql]) {
                $this->write($output, 'DROP TABLE IF EXISTS '.$this->quoteIdentifier($table, $driver).";\n");
                $this->write($output, $createSql.";\n");
                $this->writeTableRows($output, $pdo, $table, $driver);
                $this->write($output, "\n");
            }

            if ($driver === 'sqlite') {
                foreach ($this->sqliteSchemaObjects($pdo) as $schemaSql) {
                    $this->write($output, $schemaSql.";\n");
                }
                $this->write($output, "\nCOMMIT;\nPRAGMA foreign_keys=ON;\n");
            } else {
                $this->write($output, "SET FOREIGN_KEY_CHECKS=1;\n");
            }
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            fclose($output);
        }
    }

    private function writePostgresDatabaseDump(string $path, array $config): void
    {
        $process = new Process([
            'pg_dump',
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? 5432),
            '--username='.(string) ($config['username'] ?? ''),
            '--dbname='.(string) ($config['database'] ?? ''),
            '--no-owner',
            '--no-acl',
            '--clean',
            '--if-exists',
            '--file='.$path,
        ], null, [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
            'PGSSLMODE' => (string) ($config['sslmode'] ?? 'prefer'),
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('PostgreSQL backup failed: '.$process->getErrorOutput());
        }
    }

    private function mysqlTables(PDO $pdo): array
    {
        $tables = [];
        foreach ($pdo->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')->fetchAll(PDO::FETCH_NUM) as $row) {
            $table = (string) $row[0];
            $create = $pdo->query('SHOW CREATE TABLE '.$this->quoteIdentifier($table, 'mysql'))->fetch(PDO::FETCH_NUM);
            if (! isset($create[1])) {
                throw new RuntimeException('Skema tabel '.$table.' tidak dapat dibaca untuk backup.');
            }
            $tables[] = [$table, (string) $create[1]];
        }

        return $tables;
    }

    private function sqliteTables(PDO $pdo): array
    {
        $tables = [];
        $statement = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['sql'] !== null) {
                $tables[] = [(string) $row['name'], (string) $row['sql']];
            }
        }

        return $tables;
    }

    private function sqliteSchemaObjects(PDO $pdo): array
    {
        $statement = $pdo->query("SELECT sql FROM sqlite_master WHERE type IN ('index', 'trigger', 'view') AND sql IS NOT NULL ORDER BY type, name");

        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    private function writeTableRows($output, PDO $pdo, string $table, string $driver): void
    {
        $identifier = $this->quoteIdentifier($table, $driver);
        if ($driver === 'sqlite') {
            $columns = array_map(
                fn (array $column): string => (string) $column['name'],
                $pdo->query('PRAGMA table_info('.$identifier.')')->fetchAll(PDO::FETCH_ASSOC)
            );
        } else {
            $columns = [];
            foreach ($pdo->query('SHOW COLUMNS FROM '.$identifier)->fetchAll(PDO::FETCH_ASSOC) as $column) {
                if (! str_contains((string) ($column['Extra'] ?? ''), 'GENERATED')) {
                    $columns[] = (string) $column['Field'];
                }
            }
        }

        if ($columns === []) {
            return;
        }

        $selectColumns = implode(', ', array_map(
            fn (string $column): string => $this->quoteIdentifier($column, $driver),
            $columns
        ));
        $rows = $pdo->query('SELECT '.$selectColumns.' FROM '.$identifier);
        $insertPrefix = 'INSERT INTO '.$identifier.' ('.$selectColumns.') VALUES ';
        while (($row = $rows->fetch(PDO::FETCH_NUM)) !== false) {
            $values = array_map(fn ($value): string => $this->quoteValue($pdo, $value), $row);
            $this->write($output, $insertPrefix.'('.implode(', ', $values).");\n");
        }
    }

    private function addAssetImages(ZipArchive $archive): int
    {
        $count = 0;
        foreach (AssetItemImage::query()->orderBy('id')->cursor() as $image) {
            $path = str_replace('\\', '/', $image->path);
            $segments = explode('/', $path);
            if ($path === '' || str_starts_with($path, '/') || in_array('..', $segments, true)) {
                throw new RuntimeException('Path gambar aset tidak valid pada data backup.');
            }

            $disk = Storage::disk(config('filesystems.asset_images_disk'));
            if (! $disk->exists($image->path)) {
                throw new RuntimeException('Gambar aset tidak ditemukan pada penyimpanan: '.$path);
            }

            $contents = $disk->get($image->path);
            if (! is_string($contents) || ! $archive->addFromString('images/'.$path, $contents)) {
                throw new RuntimeException('Gambar aset gagal ditambahkan ke arsip: '.$path);
            }
            $count++;
        }

        return $count;
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        $quote = $driver === 'sqlite' ? '"' : '`';

        return $quote.str_replace($quote, $quote.$quote, $identifier).$quote;
    }

    private function quoteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $quoted = $pdo->quote((string) $value);
        if ($quoted === false) {
            throw new RuntimeException('Nilai database tidak dapat dienkode ke dalam file backup.');
        }

        return $quoted;
    }

    private function write($output, string $contents): void
    {
        if (fwrite($output, $contents) !== strlen($contents)) {
            throw new RuntimeException('Penulisan file backup tidak selesai.');
        }
    }
}
