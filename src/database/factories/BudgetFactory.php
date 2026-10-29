<?php

namespace Database\Factories;

use App\Enums\BudgetPeriod;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        $start = now()->startOfMonth();

        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'period_type' => BudgetPeriod::Monthly,
            'period_start' => $start->toDateString(),
            'period_end' => $start->copy()->endOfMonth()->toDateString(),
            'amount' => fake()->numberBetween(500_000, 5_000_000),
        ];
    }
}
