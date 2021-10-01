<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Office;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OfficeImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesAnOfficeImage()
    {
        Storage::fake();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        Sanctum::actingAs($user, ['office.update']);
        $response = $this->post(route('offices.images.store', $office->id), [
            'image' => UploadedFile::fake()->image('image.jpg')
        ])->assertCreated();

        Storage::assertExists($response->json('data.path'));
    }

    public function testItDeletesAnImage()
    {
        Storage::put('/image2.jpg', 'e');

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $image = $office->images()->create(['path' => 'image.jpg']);
        $image2 = $office->images()->create(['path' => 'image2.jpg']);

        Sanctum::actingAs($user, ['office.update']);
        $response = $this->deleteJson(route('offices.images.delete', [$office->id, $image2->id]))->assertOk(); //->assertSoftDeleted();

        $this->assertDeleted($image2);

        Storage::assertMissing('image2.jpg');
    }

    public function testItDoesntDeleteAnOnlyImage()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $image = $office->images()->create(['path' => 'image.jpg']);

        Sanctum::actingAs($user, ['office.update']);
        $response = $this->deleteJson(route('offices.images.delete', [$office->id, $image->id]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['image' => 'Cannot delete the only image.']);
    }

    public function testItDoesntDeleteTheFeaturedImage()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $image = $office->images()->create(['path' => 'image.jpg']);
        $featured_image = $office->images()->create(['path' => 'featuredimage.jpg']);

        $office->update(['featured_image_id' => $featured_image->id]);
        Sanctum::actingAs($user, ['office.update']);
        $response = $this->deleteJson(route('offices.images.delete', [$office->id, $featured_image->id]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['image' => 'Cannot delete the featured image.']);
    }

    public function testItDoesntDeleteAnImageBelongingToAnotherOffice()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $office2 = Office::factory()->for($user)->create();
        $image = $office2->images()->create(['path' => 'image.jpg']);

        Sanctum::actingAs($user, ['office.update']);
        $response = $this->deleteJson(route('offices.images.delete', [$office->id, $image->id]));

        $response->assertNotFound();
    }
}
