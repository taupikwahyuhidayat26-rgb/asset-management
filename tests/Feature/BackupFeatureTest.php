<?php

namespace Tests\Feature;

use App\Models\AssetItemImage;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PDO;
use Tests\TestCase;
use ZipArchive;

class BackupFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('asset_item_images');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_groups');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('role')->default('User');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('asset_item_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_item_id');
            $table->string('path');
            $table->timestamps();
        });
        Schema::create('menu_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('menu_group_id');
            $table->string('name');
            $table->string('route');
            $table->string('icon');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function test_only_admin_can_open_backup_page_or_download(): void
    {
        $this->actingAs($this->user('Staff'));
        $this->get(route('backups.index'))->assertForbidden();
        $this->post(route('backups.download'))->assertForbidden();

        $this->actingAs($this->user('Admin'));
        $this->get(route('backups.index'))->assertOk();
    }

    public function test_admin_download_contains_sql_dump_and_private_asset_images(): void
    {
        Storage::fake('remote-images');
        config(['filesystems.asset_images_disk' => 'remote-images']);
        $this->user('Admin');
        $this->actingAs(User::query()->where('role', 'Admin')->firstOrFail());

        $imagePath = 'asset-item-images/1/backup-photo.png';
        Storage::disk('remote-images')->put($imagePath, 'private image contents');
        AssetItemImage::create([
            'asset_item_id' => 1,
            'path' => $imagePath,
        ]);

        $response = $this->post(route('backups.download'))
            ->assertDownload();
        $archivePath = $response->baseResponse->getFile()->getPathname();
        $archive = new ZipArchive;

        $this->assertSame(true, $archive->open($archivePath));
        $sqlDump = $archive->getFromName('database.sql');
        $this->assertIsString($sqlDump);
        $this->assertStringContainsString('CREATE TABLE "asset_item_images"', $sqlDump);
        $this->assertStringContainsString('asset-item-images/1/backup-photo.png', $sqlDump);
        $this->assertSame('private image contents', $archive->getFromName('images/'.$imagePath));
        $this->assertNotFalse($archive->getFromName('README.txt'));
        $archive->close();
        unlink($archivePath);

        $restoredDatabase = new PDO('sqlite::memory:');
        $restoredDatabase->exec($sqlDump);
        $restoredPath = $restoredDatabase->query('SELECT path FROM asset_item_images')->fetchColumn();
        $this->assertSame($imagePath, $restoredPath);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => $role.' User',
            'username' => strtolower($role).'-backup-test',
            'email' => strtolower($role).'-backup@example.test',
            'password' => 'test-password',
            'role' => $role,
        ]);
    }
}
