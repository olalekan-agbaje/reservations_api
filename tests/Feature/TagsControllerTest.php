<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class TagsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;
    public function testItListsTags()
    {
        $response = $this->get('/api/tags');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data')[0]['id']);
    }
}
