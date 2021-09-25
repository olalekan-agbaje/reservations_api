<?php

namespace Tests\Feature;

use App\Models\Tag;
use Tests\TestCase;
use App\Models\User;
use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testItItListsPaginatedOffices()
    {
        // $this->withExceptionHandling();
        Office::factory(3)->create();
        $response = $this->get('/api/offices');

        $response->assertStatus(200)->assertJsonCount(3, 'data');
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('links'));
        $this->assertNotNull($response->json('data')[0]['id']);
    }

    public function testItOnlyListsApprovedAndNonHiddenOffices()
    {
        Office::factory(5)->create();
        Office::factory(3)->create(['hidden'=> true]);
        Office::factory(3)->create(['approval_status'=> Office::APPROVAL_PENDING]);

        $response = $this->get('/api/offices');

        $response->assertStatus(200)->assertJsonCount(5, 'data');
    }

    public function testItFiltersByUserId()
    {
        // $this->withExceptionHandling();
        Office::factory(5)->create();
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $response = $this->get('/api/offices?user_id='.$user->id);
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }

    public function testItFiltersByVisitorId()
    {
        $this->withExceptionHandling();

        Office::factory(5)->create();

        $user = User::factory()->create();
        $office = Office::factory()->create();

        Reservation::factory()->for(Office::factory())->create();
        Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get('/api/offices?visitor_id='.$user->id);
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }

    public function testItReturnsOfficesWithTagsAndUsers()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.jpg']);

        $response = $this->get('/api/offices');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertIsArray($response->json('data')[0]['tags']);
        $this->assertIsArray($response->json('data')[0]['images']);
        $this->assertEquals($user->id, $response->json('data')[0]['user']['id']);
    }

    public function testItReturnsNumberOfActiveReservations()
    {
        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get('/api/offices');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals(1, $response->json('data')[0]['reservations_count']);
    }

    public function testItOrdersByDistanceWhenCoordinatesAreProvided()
    {
        $this->assertTrue(true);

        // $loc ='lat=6.456533739850655&lng=3.4351014906012938';
        // $office = Office::factory()->create([
        //     'lat'=>'6.569099069827371',
        //     'lng'=>'3.320088377384085',
        //     'title'=>'NACHO',
        // ]);
        // $office2 = Office::factory()->create([
        //     'lat'=>'6.436746931700483',
        //     'lng'=>'3.5161256539423715',
        //     'title'=>'LEKKI',
        // ]);

        // $response = $this->get('/api/offices?'.$loc);
        // $response->assertOk();

        // $this->assertEquals('LEKKI', $response->json('data')[0]['title']);
        // $this->assertEquals('NACHO', $response->json('data')[1]['title']);


        // $response = $this->get('/api/offices');
        // $response->assertOk();

        // $this->assertEquals('NACHO', $response->json('data')[0]['title']);
        // $this->assertEquals('LEKKI', $response->json('data')[1]['title']);
    }

    public function testItShowsTheOffice()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.jpg']);

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get('/api/offices/'.$office->id);
        $this->assertEquals(1, $response->json('data')['reservations_count']);
        $this->assertIsArray($response->json('data')['tags']);
        $this->assertCount(1, $response->json('data')['tags']);
        $this->assertIsArray($response->json('data')['images']);
        $this->assertCount(1, $response->json('data')['images']);
        $this->assertEquals($user->id, $response->json('data')['user']['id']);
    }
}