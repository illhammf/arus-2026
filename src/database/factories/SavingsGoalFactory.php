<?php

namespace Database\Factories;

use App\Enums\GoalStatus;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavingsGoal>
 */
class SavingsGoalFactory extends Factory
{
    protected $model = SavingsGoal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'target_amount' => fake()->numberBetween(1_000_000, 20_000_000),
            'target_date' => now()->addMonths(6)->toDateString(),
            'status' => GoalStatus::Active,
        ];
    }
}
