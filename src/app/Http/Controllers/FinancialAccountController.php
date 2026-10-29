<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Http\Requests\FinancialAccountRequest;
use App\Models\FinancialAccount;
use App\Services\FinancialAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialAccountController extends Controller
{
    public function index(Request $request, FinancialAccountService $service): View
    {
        $accounts = $request->user()->financialAccounts()
            ->orderBy('is_archived')
            ->orderBy('name')
            ->get();

        return view('accounts.index', [
            'accounts' => $accounts,
            'balances' => $service->balancesFor($request->user(), true),
        ]);
    }

    public function create(): View
    {
        return view('accounts.form', [
            'account' => new FinancialAccount(['type' => AccountType::Cash, 'currency' => 'IDR']),
            'types' => AccountType::options(),
        ]);
    }

    public function store(FinancialAccountRequest $request): RedirectResponse
    {
        $account = $request->user()->financialAccounts()->create($request->validated());

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Akun keuangan berhasil dibuat.');
    }

    public function show(Request $request, FinancialAccount $account, FinancialAccountService $service): View
    {
        $this->authorize('view', $account);

        $transactions = $account->transactions()
            ->with('category')
            ->posted()
            ->latest('occurred_at')
            ->latest('id')
            ->paginate(10);

        $incoming = \App\Support\Money::normalize(
            $account->transactions()->posted()->whereIn('type', ['income', 'transfer_in'])->sum('amount'),
        );
        $outgoing = \App\Support\Money::normalize(
            $account->transactions()->posted()->whereIn('type', ['expense', 'transfer_out'])->sum('amount'),
        );

        return view('accounts.show', [
            'account' => $account,
            'balance' => $service->balance($account),
            'transactions' => $transactions,
            'incoming' => $incoming,
            'outgoing' => $outgoing,
        ]);
    }

    public function edit(FinancialAccount $account): View
    {
        $this->authorize('update', $account);

        return view('accounts.form', [
            'account' => $account,
            'types' => AccountType::options(),
        ]);
    }

    public function update(FinancialAccountRequest $request, FinancialAccount $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $account->update($request->validated());

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Akun keuangan berhasil diperbarui.');
    }

    public function toggleArchive(FinancialAccount $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $account->update(['is_archived' => ! $account->is_archived]);

        return back()->with('success', $account->is_archived ? 'Akun berhasil diarsipkan.' : 'Akun berhasil diaktifkan kembali.');
    }

    public function destroy(FinancialAccount $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        if ($account->transactions()->exists()) {
            return back()->with('error', 'Akun yang memiliki transaksi tidak dapat dihapus. Arsipkan akun ini sebagai gantinya.');
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Akun keuangan berhasil dihapus.');
    }
}
