<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        private readonly FinancialAccountService $accounts,
    ) {}

    public function create(User $user, array $data): Transaction
    {
        $type = TransactionType::from($data['type']);

        if ($type->isTransfer()) {
            throw ValidationException::withMessages([
                'type' => 'Gunakan formulir transfer untuk membuat transfer.',
            ]);
        }

        $account = $this->accountFor($user, (int) $data['account_id']);
        $category = $this->categoryFor($user, $data['category_id'] ?? null, $type);
        $amount = $this->positiveAmount($data['amount']);

        return DB::transaction(function () use ($user, $data, $type, $account, $category, $amount) {
            return Transaction::create([
                'user_id' => $user->id,
                'account_id' => $account->id,
                'category_id' => $category?->id,
                'type' => $type,
                'status' => TransactionStatus::Posted,
                'amount' => $amount,
                'currency' => $account->currency,
                'occurred_at' => Carbon::parse($data['occurred_at']),
                'description' => $this->description($data, $category),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * @return array{0: Transaction, 1: Transaction}
     */
    public function createTransfer(User $user, array $data): array
    {
        $source = $this->accountFor($user, (int) $data['source_account_id']);
        $destination = $this->accountFor($user, (int) $data['destination_account_id']);

        if ($source->is($destination)) {
            throw ValidationException::withMessages([
                'destination_account_id' => 'Akun sumber dan tujuan harus berbeda.',
            ]);
        }

        $amount = $this->positiveAmount($data['amount']);
        $occurredAt = Carbon::parse($data['occurred_at']);
        $groupId = (string) Str::uuid();
        $description = trim((string) ($data['description'] ?? ''))
            ?: 'Transfer ' . $source->name . ' ke ' . $destination->name;

        return DB::transaction(function () use ($user, $source, $destination, $amount, $occurredAt, $groupId, $description, $data) {
            $out = Transaction::create([
                'user_id' => $user->id,
                'account_id' => $source->id,
                'category_id' => null,
                'type' => TransactionType::TransferOut,
                'status' => TransactionStatus::Posted,
                'amount' => $amount,
                'currency' => $source->currency,
                'occurred_at' => $occurredAt,
                'description' => $description,
                'notes' => $data['notes'] ?? null,
                'transfer_group_id' => $groupId,
            ]);

            $in = Transaction::create([
                'user_id' => $user->id,
                'account_id' => $destination->id,
                'category_id' => null,
                'type' => TransactionType::TransferIn,
                'status' => TransactionStatus::Posted,
                'amount' => $amount,
                'currency' => $destination->currency,
                'occurred_at' => $occurredAt,
                'description' => $description,
                'notes' => $data['notes'] ?? null,
                'transfer_group_id' => $groupId,
            ]);

            return [$out, $in];
        });
    }

    public function update(User $user, Transaction $transaction, array $data): Transaction
    {
        if ($transaction->user_id !== $user->id) {
            abort(404);
        }

        if ($transaction->isTransfer()) {
            return $this->updateTransfer($user, $transaction, $data);
        }

        $type = TransactionType::from($data['type']);

        if ($type->isTransfer()) {
            throw ValidationException::withMessages([
                'type' => 'Gunakan formulir transfer untuk mengubah transfer.',
            ]);
        }

        $account = $this->accountFor($user, (int) $data['account_id']);
        $category = $this->categoryFor($user, $data['category_id'] ?? null, $type);
        $amount = $this->positiveAmount($data['amount']);

        return DB::transaction(function () use ($transaction, $data, $type, $account, $category, $amount) {
            $transaction->update([
                'account_id' => $account->id,
                'category_id' => $category?->id,
                'type' => $type,
                'amount' => $amount,
                'currency' => $account->currency,
                'occurred_at' => Carbon::parse($data['occurred_at']),
                'description' => $this->description($data, $category),
                'notes' => $data['notes'] ?? null,
            ]);

            return $transaction->refresh();
        });
    }

    public function updateTransfer(User $user, Transaction $transaction, array $data): Transaction
    {
        if (! $transaction->transfer_group_id) {
            abort(404);
        }

        $source = $this->accountFor($user, (int) $data['source_account_id']);
        $destination = $this->accountFor($user, (int) $data['destination_account_id']);

        if ($source->is($destination)) {
            throw ValidationException::withMessages([
                'destination_account_id' => 'Akun sumber dan tujuan harus berbeda.',
            ]);
        }

        $amount = $this->positiveAmount($data['amount']);
        $occurredAt = Carbon::parse($data['occurred_at']);
        $description = trim((string) ($data['description'] ?? ''))
            ?: 'Transfer ' . $source->name . ' ke ' . $destination->name;

        return DB::transaction(function () use ($user, $transaction, $source, $destination, $amount, $occurredAt, $description, $data) {
            $rows = Transaction::query()
                ->where('user_id', $user->id)
                ->where('transfer_group_id', $transaction->transfer_group_id)
                ->get();

            $out = $rows->firstWhere('type', TransactionType::TransferOut);
            $in = $rows->firstWhere('type', TransactionType::TransferIn);

            if (! $out || ! $in) {
                throw ValidationException::withMessages([
                    'amount' => 'Pasangan transfer tidak lengkap dan tidak dapat diubah.',
                ]);
            }

            $out->update([
                'account_id' => $source->id,
                'amount' => $amount,
                'currency' => $source->currency,
                'occurred_at' => $occurredAt,
                'description' => $description,
                'notes' => $data['notes'] ?? null,
            ]);

            $in->update([
                'account_id' => $destination->id,
                'amount' => $amount,
                'currency' => $destination->currency,
                'occurred_at' => $occurredAt,
                'description' => $description,
                'notes' => $data['notes'] ?? null,
            ]);

            return $out->refresh();
        });
    }

    public function delete(User $user, Transaction $transaction): void
    {
        if ($transaction->user_id !== $user->id) {
            abort(404);
        }

        DB::transaction(function () use ($user, $transaction) {
            if ($transaction->isTransfer() && $transaction->transfer_group_id) {
                Transaction::query()
                    ->where('user_id', $user->id)
                    ->where('transfer_group_id', $transaction->transfer_group_id)
                    ->delete();

                return;
            }

            $transaction->delete();
        });
    }

    public function accountFor(User $user, int $accountId): FinancialAccount
    {
        return $user->financialAccounts()
            ->where('is_archived', false)
            ->findOrFail($accountId);
    }

    public function positiveAmount(mixed $amount): string
    {
        $normalized = Money::normalize($amount);

        if (! Money::isPositive($normalized)) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal harus lebih besar dari nol.',
            ]);
        }

        return $normalized;
    }

    private function categoryFor(User $user, mixed $categoryId, TransactionType $type): ?Category
    {
        if (! $categoryId) {
            return null;
        }

        $category = Category::query()
            ->availableFor($user)
            ->active()
            ->findOrFail((int) $categoryId);

        $expected = $type === TransactionType::Income
            ? CategoryType::Income
            : CategoryType::Expense;

        if ($category->type !== $expected) {
            throw ValidationException::withMessages([
                'category_id' => 'Kategori tidak sesuai dengan jenis transaksi.',
            ]);
        }

        return $category;
    }

    private function description(array $data, ?Category $category): string
    {
        $description = trim((string) ($data['description'] ?? ''));

        if ($description !== '') {
            return $description;
        }

        return $category?->name ?? 'Transaksi';
    }
}
