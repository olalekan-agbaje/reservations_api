<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Office::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'=> User::factory(),
            'title'=> $this->faker->sentence(3),
            'description'=> $this->faker->paragraph(),
            'lat'=> $this->faker->latitude(),
            'lng'=> $this->faker->longitude(),
            'address_line1'=> $this->faker->address(),
            'approval_status'=>Office::APPROVAL_APPROVED,
            'hidden'=>false,
            'price_per_day'=> $this->faker->numberBetween(10_000, 20_000),
            'monthly_discount'=>0
        ];
    }
}
