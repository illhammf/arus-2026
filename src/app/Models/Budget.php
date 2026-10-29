<?php

namespace App\Models;

use App\Enums\BudgetPeriod;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'period_type',
        'period_start',
        'period_end',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_type' => BudgetPeriod::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function spent(): string
    {
        $query = $this->user->transactions()
            ->expense()
            ->posted()
            ->whereBetween('occurred_at', [
                $this->period_start->copy()->startOfDay(),
                $this->period_end->copy()->endOfDay(),
            ]);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        return Money::normalize($query->sum('amount'));
    }

    public function remaining(): string
    {
        return Money::sub($this->amount, $this->spent());
    }

    public function usagePercentage(): float
    {
        $amount = (float) $this->amount;

        if ($amount <= 0) {
            return 0.0;
        }

        return min(100, round(((float) $this->spent() / $amount) * 100, 1));
    }

    public function isOverBudget(): bool
    {
        return Money::compare($this->spent(), $this->amount) > 0;
    }
}
