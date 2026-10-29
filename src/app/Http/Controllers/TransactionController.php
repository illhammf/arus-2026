<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\TransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $transactions = $user->transactions()
            ->with(['account', 'category'])
            ->posted()
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('account_id'), fn ($query) => $query->where('account_id', $request->integer('account_id')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('occurred_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('occurred_at', '<=', $request->date('to')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->string('search')->trim() . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', $search)
                        ->orWhere('notes', 'like', $search);
                });
            })
            ->latest('occurred_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'accounts' => $user->financialAccounts()->orderBy('name')->get(),
            'categories' => Category::query()->availableFor($user)->active()->orderBy('name')->get(),
            'types' => TransactionType::options(),
            'filters' => $request->only(['type', 'account_id', 'category_id', 'from', 'to', 'search']),
            'income' => $this->periodSum($user, TransactionType::Income),
            'expense' => $this->periodSum($user, TransactionType::Expense),
        ]);
    }

    public function create(Request $request): View
    {
        $accounts = $request->user()->financialAccounts()->where('is_archived', false)->orderBy('name')->get();
        $selectedAccount = $request->integer('account_id') ?: $accounts->first()?->id;

        return view('transactions.form', [
            'transaction' => new Transaction([
                'type' => $request->query('type') === 'income' ? TransactionType::Income : TransactionType::Expense,
                'occurred_at' => now(),
                'account_id' => $selectedAccount,
            ]),
            'accounts' => $accounts,
            'categories' => Category::query()->availableFor($request->user())->active()->orderBy('name')->get(),
            'types' => TransactionType::simpleOptions(),
        ]);
    }

    public function store(TransactionRequest $request, TransactionService $service): RedirectResponse
    {
        $transaction = $service->create($request->user(), $request->validated());

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);
        $transaction->load(['account', 'category']);

        return view('transactions.show', [
            'transaction' => $transaction,
            'pair' => $transaction->isTransfer() ? $transaction->pairedTransfer() : null,
        ]);
    }

    public function edit(Transaction $transaction): View|RedirectResponse
    {
        $this->authorize('update', $transaction);

        if ($transaction->isTransfer()) {
            return redirect()->route('transfers.edit', $transaction);
        }

        return view('transactions.form', [
            'transaction' => $transaction,
            'accounts' => $transaction->user->financialAccounts()->where('is_archived', false)->orderBy('name')->get(),
            'categories' => Category::query()->availableFor($transaction->user)->active()->orderBy('name')->get(),
            'types' => TransactionType::simpleOptions(),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $service->update($request->user(), $transaction, $request->validated());

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $service->delete(request()->user(), $transaction);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    private function periodSum($user, TransactionType $type): string
    {
        return \App\Support\Money::normalize(
            $user->transactions()
                ->where('type', $type->value)
                ->posted()
                ->whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
        );
    }
}
