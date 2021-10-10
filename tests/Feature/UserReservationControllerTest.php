<?php

namespace Tests\Feature;

use App\Models\Office;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reservation;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class UserReservationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testItListsReservationsThatBelongToTheUser()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $reservation = Reservation::factory()->for($user)->create();

        $image = $reservation->office->images()->create([
            'path' => 'reservation_image.jpg'
        ]);

        $reservation->office()->update(['featured_image_id' => $image->id]);
        $reservation = Reservation::factory()->for($user)->count(2)->create();
        $reservation = Reservation::factory()->count(5)->create();

        Sanctum::actingAs($user, ['reservations.show']);

        $response = $this->getJson(route('reservation.index'))->assertOk();

        $response->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonStructure(['data' => ['*' => ['id', 'office' => ['featured_image_id', 'featured_image']]]])
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.office.featured_image.id', $image->id);
    }

    public function testItListsReservationsByDateRange()
    {
        // $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $startDate = "2021-03-03";
        $endDate = "2021-04-04";

        $reservations = Reservation::factory()->for($user)->createMany(
            [
                [
                    'start_date' => "2021-03-04",
                    'end_date' => "2021-03-15",
                ],
                [
                    'start_date' => "2021-03-05",
                    'end_date' => "2021-04-15",
                ],
                [
                    'start_date' => "2021-02-25",
                    'end_date' => "2021-03-29",
                ],
                [
                    'start_date' => "2021-02-25",
                    'end_date' => "2021-04-29",
                ]
            ]
        );

        $reservation = Reservation::factory()->create([
            'start_date' => "2021-02-25",
            'end_date' => "2021-03-29",
        ]);
        $reservation = Reservation::factory()->for($user)->create([
            'start_date' => "2021-02-01",
            'end_date' => "2021-02-05",
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson(route('reservation.index') . '?' . http_build_query(
            [
                'from_date' => $startDate,
                'to_date' => $endDate
            ]
        ));
        $response->assertJsonCount(4, 'data');
        $this->assertEquals($reservations->pluck('id')->toArray(), collect($response->json('data'))->pluck('id')->toArray());
    }

    public function testItFiltersReservationByStatus()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory(2)->for($user)->canceled()->create();
        $reservation = Reservation::factory()->for($user)->create();

        Sanctum::actingAs($user, ['*']);
        $response = $this->getJson(route('reservation.index') . '?' . http_build_query(
            [
                'status' => Reservation::STATUS_ACTIVE,
            ]
        ))->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reservation->id);
    }

    public function testItFiltersReservationByOffice()
    {
        $user = User::factory()->create();
        $office = Office::factory()->create();
        $reservation = Reservation::factory(2)->for($office)->for($user)->create();

        // $reservation = Reservation::factory()->for($user)->create();

        Sanctum::actingAs($user, ['*']);
        $response = $this->getJson(route('reservation.index') . '?' . http_build_query(
            [
                'office_id' => $office->id,
            ]
        ))->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $reservation[0]->id)
            ->assertJsonPath('data.1.id', $reservation[1]->id)
            ->assertJsonPath('data.0.office.id', $office->id)
            ->assertJsonPath('data.1.office.id', $office->id);
    }
}
