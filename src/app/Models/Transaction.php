<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'status',
        'amount',
        'currency',
        'occurred_at',
        'description',
        'notes',
        'transfer_group_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::Posted->value);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Income->value);
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Expense->value);
    }

    public function scopeTransfer(Builder $query): Builder
    {
        return $query->whereIn('type', [
            TransactionType::TransferOut->value,
            TransactionType::TransferIn->value,
        ]);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function isTransfer(): bool
    {
        return (bool) $this->type?->isTransfer();
    }

    public function isIncome(): bool
    {
        return $this->type === TransactionType::Income;
    }

    public function isExpense(): bool
    {
        return $this->type === TransactionType::Expense;
    }

    public function signedAmount(): string
    {
        $amount = Money::normalize($this->amount);

        return $this->type?->sign() === -1 ? '-' . $amount : $amount;
    }

    public function pairedTransfer(): ?self
    {
        if (! $this->isTransfer() || ! $this->transfer_group_id) {
            return null;
        }

        return self::query()
            ->where('transfer_group_id', $this->transfer_group_id)
            ->where('id', '!=', $this->id)
            ->first();
    }

    public function getFormattedAmountAttribute(): string
    {
        return Money::format($this->amount, $this->currency ?? 'IDR');
    }
}
