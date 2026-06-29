<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketTier>
 */
class TicketTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::query()->create([
                'name' => $this->faker->sentence(3),
            ])->id,
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->randomFloat(2, 0, 500),
            'quantity' => $this->faker->numberBetween(1, 500),
            'sales_channels' => $this->faker->optional()->randomElements(TicketTier::SALES_CHANNELS, 2),
            'is_published' => false,
            'is_active' => true,
        ];
    }
}
