<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+1 month');

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'location' => fake()->city(),
            'color' => fake()->hexColor(),
            'is_all_day' => fake()->boolean(20),
            'user_id' => User::factory(),
            'has_reminder' => fake()->boolean(30),
            'reminder_minutes' => fake()->randomElement([15, 30, 60, 120]),
        ];
    }
}
