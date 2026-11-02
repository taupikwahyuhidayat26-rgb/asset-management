<?php

namespace Tests\Feature;

use App\Models\AssetItem;
use App\Models\AssetItemImage;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AssetItemImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('asset_item_images');
        Schema::dropIfExists('asset_items');

        Schema::create('asset_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->string('sku');
            $table->string('serial_number')->nullable();
            $table->unsignedBigInteger('droppoint_id')->nullable();
            $table->string('status')->default('Ready');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_item_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_item_id');
            $table->string('path');
            $table->timestamps();
        });
    }

    public function test_staff_can_upload_multiple_private_images_for_an_asset_item(): void
    {
        Storage::fake('local');
        $this->actingAs($this->staff());
        $item = $this->assetItem();

        $response = $this->post(route('asset_item.images.store', $item), [
            'images' => [
                UploadedFile::fake()->image('unit-front.jpg'),
                UploadedFile::fake()->image('unit-back.png'),
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('asset_item_images', 2);

        foreach (AssetItemImage::all() as $image) {
            Storage::disk('local')->assertExists($image->path);
        }
    }

    public function test_asset_images_use_the_configured_private_disk(): void
    {
        Storage::fake('remote-images');
        config(['filesystems.asset_images_disk' => 'remote-images']);
        $this->actingAs($this->staff());
        $item = $this->assetItem();

        $this->post(route('asset_item.images.store', $item), [
            'images' => [UploadedFile::fake()->image('unit.png')],
        ])->assertRedirect();

        $image = AssetItemImage::firstOrFail();
        Storage::disk('remote-images')->assertExists($image->path);
        $this->get(route('asset_item.images.show', [$item, $image]))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_non_image_files_are_rejected(): void
    {
        Storage::fake('local');
        $this->actingAs($this->staff());
        $item = $this->assetItem();

        $response = $this->post(route('asset_item.images.store', $item), [
            'images' => [UploadedFile::fake()->create('payload.php', 10, 'text/plain')],
        ]);

        $response->assertSessionHasErrors('images.0');
        $this->assertDatabaseCount('asset_item_images', 0);
    }

    public function test_uploaded_images_are_served_through_the_authenticated_route(): void
    {
        Storage::fake('local');
        $this->actingAs($this->staff());
        $item = $this->assetItem();

        $this->post(route('asset_item.images.store', $item), [
            'images' => [UploadedFile::fake()->image('unit.png')],
        ])->assertRedirect();

        $image = AssetItemImage::firstOrFail();
        $this->get(route('asset_item.images.show', [$item, $image]))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_image_route_requires_login(): void
    {
        Storage::fake('local');
        $item = $this->assetItem();
        $image = $item->images()->create(['path' => 'asset-item-images/'.$item->id.'/unit.png']);

        $this->get(route('asset_item.images.show', [$item, $image]))
            ->assertRedirect(route('login'));
    }

    public function test_user_role_cannot_upload_images(): void
    {
        Storage::fake('local');
        $this->withoutExceptionHandling();
        $user = new User;
        $user->id = 2;
        $user->role = 'User';
        $this->actingAs($user);
        $item = $this->assetItem();

        try {
            $this->post(route('asset_item.images.store', $item), [
                'images' => [UploadedFile::fake()->image('unit.jpg')],
            ]);
            $this->fail('Users without data management access must not upload images.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertDatabaseCount('asset_item_images', 0);
    }

    public function test_staff_can_delete_an_image_and_its_private_file(): void
    {
        Storage::fake('remote-images');
        config(['filesystems.asset_images_disk' => 'remote-images']);
        $this->actingAs($this->staff());
        $item = $this->assetItem();
        $path = 'asset-item-images/'.$item->id.'/unit.jpg';
        Storage::disk('remote-images')->put($path, 'image contents');
        $image = $item->images()->create(['path' => $path]);

        $response = $this->delete(route('asset_item.images.destroy', [$item, $image]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('asset_item_images', ['id' => $image->id]);
        Storage::disk('remote-images')->assertMissing($path);
    }

    private function staff(): User
    {
        $user = new User;
        $user->id = 1;
        $user->role = 'Staff';

        return $user;
    }

    private function assetItem(): AssetItem
    {
        return AssetItem::create([
            'asset_id' => 1,
            'sku' => 'UNIT-001',
            'status' => 'Ready',
        ]);
    }
}
