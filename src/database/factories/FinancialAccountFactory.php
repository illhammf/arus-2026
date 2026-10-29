<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialAccount>
 */
class FinancialAccountFactory extends Factory
{
    protected $model = FinancialAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(AccountType::cases()),
            'currency' => 'IDR',
            'opening_balance' => fake()->numberBetween(0, 5_000_000),
            'opening_balance_date' => now()->toDateString(),
            'is_archived' => false,
            'color' => fake()->hexColor(),
        ];
    }
}
