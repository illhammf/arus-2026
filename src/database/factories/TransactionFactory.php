<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $account = FinancialAccount::factory();

        return [
            'user_id' => fn (array $attributes) => FinancialAccount::find($attributes['account_id'])?->user_id,
            'account_id' => $account,
            'category_id' => null,
            'type' => fake()->randomElement([TransactionType::Income, TransactionType::Expense]),
            'status' => TransactionStatus::Posted,
            'amount' => fake()->numberBetween(10_000, 1_000_000),
            'currency' => 'IDR',
            'occurred_at' => now(),
            'description' => fake()->sentence(3),
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => ['type' => TransactionType::Income]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => ['type' => TransactionType::Expense]);
    }
}
