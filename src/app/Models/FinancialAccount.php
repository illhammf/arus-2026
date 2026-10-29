<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency',
        'opening_balance',
        'opening_balance_date',
        'is_archived',
        'color',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'opening_balance' => 'decimal:2',
            'opening_balance_date' => 'date',
            'is_archived' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function signedTransactionTotal(): string
    {
        $total = $this->transactions()
            ->posted()
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('income', 'transfer_in') THEN amount ELSE -amount END), 0) as total")
            ->value('total');

        return Money::normalize($total);
    }

    public function balance(): string
    {
        return Money::add($this->opening_balance, $this->signedTransactionTotal());
    }

    public function isArchived(): bool
    {
        return (bool) $this->is_archived;
    }
}
