<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureDataAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DataAccessTest extends TestCase
{
    public function test_user_role_cannot_submit_asset_changes(): void
    {
        $request = Request::create('/asset', 'POST');
        $user = new User;
        $user->role = 'User';
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);
        (new EnsureDataAccess)->handle($request, fn () => new Response, 'manage');
    }

    public function test_staff_role_can_reach_data_write_routes(): void
    {
        $request = Request::create('/asset', 'POST');
        $staff = new User;
        $staff->role = 'Staff';
        $request->setUserResolver(fn () => $staff);

        $response = (new EnsureDataAccess)->handle($request, fn () => new Response('allowed'), 'manage');

        $this->assertSame('allowed', $response->getContent());
    }

    public function test_initial_admin_email_receives_admin_access(): void
    {
        config(['access.initial_admin_email' => 'admin@example.com']);

        $admin = new User(['email' => 'ADMIN@example.com']);
        $admin->role = 'User';

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->canManageData());
    }

    public function test_role_control_views_compile(): void
    {
        $files = [
            'resources/views/asset/index.blade.php',
            'resources/views/categories/index.blade.php',
            'resources/views/reports/inventory.blade.php',
            'resources/views/backups/index.blade.php',
            'resources/views/asset/kartu_stok.blade.php',
            'resources/views/asset_item/index.blade.php',
            'resources/views/inventory_movements/index.blade.php',
            'resources/views/inventory_movements/create.blade.php',
            'resources/views/inventory_movements/show.blade.php',
            'resources/views/supplier/index.blade.php',
            'resources/views/droppoint/index.blade.php',
            'resources/views/employees/index.blade.php',
            'resources/views/employees/transactions/_list.blade.php',
            'resources/views/employees/transactions.blade.php',
            'resources/views/employees/transactions/system_id/system_id.blade.php',
            'resources/views/employees/transactions/resignation/resignation.blade.php',
            'resources/views/employees/transactions/position_mutation/position_mutation.blade.php',
            'resources/views/employees/transactions/droppoint_mutation/droppoint_mutation.blade.php',
            'resources/views/barang_masuk/index.blade.php',
            'resources/views/barang_keluar/index.blade.php',
            'resources/views/layouts/app.blade.php',
            'resources/views/dashboard.blade.php',
            'resources/views/users/index.blade.php',
        ];

        foreach ($files as $file) {
            $compiled = app('blade.compiler')->compileString(file_get_contents(base_path($file)));
            $this->assertNotEmpty($compiled, "Blade view did not compile: {$file}");
        }
    }

    public function test_only_admin_can_pass_admin_middleware(): void
    {
        $staffRequest = Request::create('/users');
        $staff = new User;
        $staff->role = 'Staff';
        $staffRequest->setUserResolver(fn () => $staff);

        try {
            (new EnsureAdmin)->handle($staffRequest, fn () => new Response);
            $this->fail('Staff must not be allowed through admin middleware.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $adminRequest = Request::create('/users');
        $admin = new User;
        $admin->role = 'Admin';
        $adminRequest->setUserResolver(fn () => $admin);
        $response = (new EnsureAdmin)->handle($adminRequest, fn () => new Response('allowed'));

        $this->assertSame('allowed', $response->getContent());
    }

    public function test_mixed_password_hash_algorithms_can_be_verified_and_rehashed(): void
    {
        config([
            'hashing.driver' => 'bcrypt',
            'hashing.bcrypt.verify' => false,
        ]);

        $plainPassword = 'temporary-test-password';
        $argonHash = password_hash($plainPassword, PASSWORD_ARGON2ID);

        $this->assertTrue(Hash::check($plainPassword, $argonHash));
        $this->assertTrue(Hash::needsRehash($argonHash));
    }
}
