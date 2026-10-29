<?php

namespace Database\Factories;

use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavingsContribution>
 */
class SavingsContributionFactory extends Factory
{
    protected $model = SavingsContribution::class;

    public function definition(): array
    {
        return [
            'savings_goal_id' => SavingsGoal::factory(),
            'user_id' => fn (array $attributes) => SavingsGoal::find($attributes['savings_goal_id'])?->user_id,
            'amount' => fake()->numberBetween(100_000, 1_000_000),
            'contributed_at' => now(),
        ];
    }
}
