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

        Storage::disk()->assertExists($response->json('data.path'));
    }
}
