<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function create(Request $request): View
    {
        $accounts = $request->user()->financialAccounts()->where('is_archived', false)->orderBy('name')->get();
        $sourceAccountId = $request->integer('source_account_id') ?: $accounts->first()?->id;

        return view('transfers.form', [
            'transaction' => null,
            'sourceAccountId' => $sourceAccountId,
            'destinationAccountId' => null,
            'accounts' => $accounts,
        ]);
    }

    public function store(TransferRequest $request, TransactionService $service): RedirectResponse
    {
        [$out] = $service->createTransfer($request->user(), $request->validated());

        return redirect()->route('transactions.show', $out)
            ->with('success', 'Transfer berhasil dicatat.');
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        if (! $transaction->isTransfer() || ! $transaction->transfer_group_id) {
            abort(404);
        }

        $rows = $request->user()->transactions()
            ->where('transfer_group_id', $transaction->transfer_group_id)
            ->get();

        $out = $rows->firstWhere('type', \App\Enums\TransactionType::TransferOut);
        $in = $rows->firstWhere('type', \App\Enums\TransactionType::TransferIn);

        abort_unless($out && $in, 404);

        return view('transfers.form', [
            'transaction' => $out,
            'sourceAccountId' => $out->account_id,
            'destinationAccountId' => $in->account_id,
            'accounts' => $request->user()->financialAccounts()->where('is_archived', false)->orderBy('name')->get(),
        ]);
    }

    public function update(TransferRequest $request, Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $service->updateTransfer($request->user(), $transaction, $request->validated());

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transfer berhasil diperbarui.');
    }
}
