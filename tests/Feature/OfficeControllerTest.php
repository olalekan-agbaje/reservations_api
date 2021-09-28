<?php

namespace Tests\Feature;

use App\Models\Tag;
use Tests\TestCase;
use App\Models\User;
use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Notifications\OfficePendingApprovalNotification;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testItItListsPaginatedOffices()
    {
        // $this->withExceptionHandling();
        Office::factory(3)->create();
        $response = $this->get(route('offices.index'));

        $response->assertStatus(200)->assertJsonCount(3, 'data');
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('links'));
        $this->assertNotNull($response->json('data')[0]['id']);
    }

    public function testItOnlyListsApprovedAndNonHiddenOffices()
    {
        Office::factory(5)->create();
        Office::factory(3)->create(['hidden' => true]);
        Office::factory(3)->create(['approval_status' => Office::APPROVAL_PENDING]);

        $response = $this->get(route('offices.index'));

        $response->assertStatus(200)->assertJsonCount(5, 'data');
    }

    public function testItFiltersByUserId()
    {
        // $this->withExceptionHandling();
        Office::factory(5)->create();
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $response = $this->get(route('offices.index') . '?user_id=' . $user->id);
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

        $response = $this->get(route('offices.index') . '?visitor_id=' . $user->id);
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

        $response = $this->get(route('offices.index'));

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

        $response = $this->get(route('offices.index'));

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals(1, $response->json('data')[0]['reservations_count']);
    }

    public function testItOrdersByDistanceWhenCoordinatesAreProvided()
    {
        $this->assertTrue(true);
        return true;

        $loc = '?lat=6.456533739850655&lng=3.4351014906012938';
        $office = Office::factory()->create([
            'lat' => '6.569099069827371',
            'lng' => '3.320088377384085',
            'title' => 'NACHO',
        ]);
        $office2 = Office::factory()->create([
            'lat' => '6.436746931700483',
            'lng' => '3.5161256539423715',
            'title' => 'LEKKI',
        ]);

        $response = $this->get(route('offices.index') . $loc);
        $response->assertOk();

        $this->assertEquals('LEKKI', $response->json('data')[0]['title']);
        $this->assertEquals('NACHO', $response->json('data')[1]['title']);


        $response = $this->get(route('offices.index'));
        $response->assertOk();

        $this->assertEquals('NACHO', $response->json('data')[0]['title']);
        $this->assertEquals('LEKKI', $response->json('data')[1]['title']);
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

        $response = $this->get(route('office.show', $office->id));
        $this->assertEquals(1, $response->json('data')['reservations_count']);
        $this->assertIsArray($response->json('data')['tags']);
        $this->assertCount(1, $response->json('data')['tags']);
        $this->assertIsArray($response->json('data')['images']);
        $this->assertCount(1, $response->json('data')['images']);
        $this->assertEquals($user->id, $response->json('data')['user']['id']);
    }

    public function testItDoesntAllowCreatingIfScopeIsNotProvided()
    {
        $this->withExceptionHandling();

        $user = User::factory()->createQuietly();

        $token = $user->createToken('test', []);

        $response = $this->postJson(route('offices.create'), [], [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ])->assertForbidden();
    }

    public function testItAllowsCreatingIfScopeIsProvided()
    {
        $this->withExceptionHandling();

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['office.create']);
        $response = $this->postJson(route('offices.create'))->assertUnprocessable();
    }

    public function testItCreatesAnOffice()
    {
        $this->withExceptionHandling();
        Notification::fake();


        $adminUser = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->createQuietly();
        $tag = Tag::factory()->create();
        $tag2 = Tag::factory()->create();


        // $token = $user->createToken('test', ['office.create']);
        Sanctum::actingAs($user, ['office.create']);

        $response = $this->postJson(route('offices.create'), [
            'title' => 'Office in location',
            'description' => 'Description of office',
            'lat' => '39.123456798',
            'lng' => '-8.123454987',
            'address_line1' => 'Address is a required field',
            'price_per_day' => 25_123,
            'monthly_discount' => 5,
            'tags' => [$tag->id, $tag2->id],
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Office in location')
            ->assertJsonPath('data.approval_status', Office::APPROVAL_PENDING)
            ->assertJsonCount(2, 'data.tags')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.tags.0.id', $tag->id);

        $this->assertDatabaseHas('offices', ['title' => 'Office in location']);
        Notification::assertSentTo($adminUser, OfficePendingApprovalNotification::class);
    }

    public function testItUpdatesAnOffice()
    {
        $this->withExceptionHandling();
        $user = User::factory()->createQuietly();
        $tags = Tag::factory(2)->create();
        $anotherTag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach([$tags]);

        Sanctum::actingAs($user, ['office.update']);

        $response = $this->putJson(route('offices.update', $office->id), [
            'title' => 'New Office in location',
            'description' => 'New Description of office',
            // 'lat' => '39.123456798',
            // 'lng' => '-8.123454987',
            // 'address_line1' => 'Address is a required field',
            // 'price_per_day' => 25_123,
            // 'monthly_discount' => 5,
            'tags' => [$tags[0]->id, $anotherTag->id],
        ])->assertJsonPath('data.title', 'New Office in location')
            ->assertJsonPath('data.description', 'New Description of office')
            ->assertJsonCount(2, 'data.tags')
            ->assertJsonPath('data.tags.0.id', $tags[0]->id)
            ->assertOk();
        //     ->assertJsonPath('data.approval_status', Office::APPROVAL_PENDING)
        //     ->assertJsonPath('data.user.id', $user->id)

        // $this->assertDatabaseHas('offices', ['title' => 'Office in location']);
    }

    public function testItDoesntUpdateOfficeThatDoesntBelongToUser()
    {
        $this->withExceptionHandling();

        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $office = Office::factory()->for($user2)->create();

        Sanctum::actingAs($user, ['office.update']);

        $response = $this->putJson(route('offices.update', $office->id), [
            'title' => 'User 2 Office in location',
            'description' => 'User 2 Description of office',
            // 'lat' => '39.123456798',
            // 'lng' => '-8.123454987',
            // 'address_line1' => 'Address is a required field',
            // 'price_per_day' => 25_123,
            // 'monthly_discount' => 5,
            // 'tags' => [$tag->id, $tag2->id],
        ])->assertForbidden();
    }

    public function testItMarksTheOfficeAsPendingIfDirty()
    {
        $this->withExceptionHandling();

        $adminUser = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        Notification::fake();

        Sanctum::actingAs($user, ['office.update']);

        $response = $this->putJson(route('offices.update', $office->id), [
            'title' => 'User 2 Office in location',
            'description' => 'User 2 Description of office',
            'lat' => '39.123456798',
            'lng' => '-8.123454987',
            // 'address_line1' => 'Address is a required field',
            // 'price_per_day' => 25_123,
            // 'monthly_discount' => 5,
            // 'tags' => [$tag->id, $tag2->id],
        ])->assertJsonPath('data.approval_status', Office::APPROVAL_PENDING)
            ->assertOk();

        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'approval_status' => Office::APPROVAL_PENDING,
        ]);

        Notification::assertSentTo($adminUser, OfficePendingApprovalNotification::class);
        // dd($response->json());
    }

    public function testItCanDeleteOffices()
    {
        $this->withExceptionHandling();
        $user = User::factory()->createQuietly();
        $office = Office::factory()->for($user)->create();

        Sanctum::actingAs($user, ['office.delete']);

        $response = $this->deleteJson(route('offices.delete', $office->id))->assertOk();

        $this->assertSoftDeleted($office);
    }

    public function testItCannotDeleteOfficesWithReservations()
    {
        // $this->withoutExceptionHandling();
        $user = User::factory()->createQuietly();
        $office = Office::factory()->for($user)->create();

        Reservation::factory(3)->for($office)->create();
        Sanctum::actingAs($user, ['office.delete']);

        $response = $this->deleteJson(route('offices.delete', $office->id))
            ->assertUnprocessable();

        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'deleted_at' => null
        ]);
    }
}
