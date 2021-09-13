<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Office;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsOffices()
    {
        // $this->withExceptionHandling();
        Office::factory(3)->create();
        $response = $this->get('/api/offices');
        $this->assertNotNull($response->json('data')[0]['id']);
        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }
}
