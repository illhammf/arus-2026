<?php

namespace App\Models;

use App\Enums\GoalStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'target_date',
        'status',
        'linked_account_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'target_date' => 'date',
            'status' => GoalStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'linked_account_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class);
    }

    public function contributedTotal(): string
    {
        return Money::normalize($this->contributions()->sum('amount'));
    }

    public function remaining(): string
    {
        return Money::sub($this->target_amount, $this->contributedTotal());
    }

    public function progressPercentage(): float
    {
        $target = (float) $this->target_amount;

        if ($target <= 0) {
            return 0.0;
        }

        return min(100, round(((float) $this->contributedTotal() / $target) * 100, 1));
    }

    public function isCompleted(): bool
    {
        return $this->status === GoalStatus::Completed
            || Money::compare($this->contributedTotal(), $this->target_amount) >= 0;
    }
}
