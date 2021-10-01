<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testItListsReservationsThatBelongToTheUser()
    {
     	$this->withoutExceptionHandling();

        $user = User::factory()->create();
        $reservation = Reservation::factory()->for($user)->create();

        $image = $reservation->office->images()->create([
            'path'=>'reservation_image.jpg'
        ]);
        
        $reservation->office()->update(['featured_image_id' => $image->id]);
        $reservation = Reservation::factory()->for($user)->count(2)->create();
        $reservation = Reservation::factory()->count(5)->create();
        
        Sanctum::actingAs($user,['reservations.show']);

        $response = $this->getJson(route('reservation.index'))->assertOk();

        $response->assertJsonStructure(['data','links','meta'])
        ->assertJsonStructure(['data'=>['*'=>['id','office'=>['featured_image_id','featured_image']]]])
        ->assertJsonCount(3,'data')
        ->assertJsonPath('data.0.office.featured_image.id',$image->id);    
    }
}
