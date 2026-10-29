<?php

namespace App\Http\Controllers;

use App\Enums\BudgetPeriod;
use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $budgets = $request->user()->budgets()
            ->with('category')
            ->orderByDesc('period_start')
            ->get();

        return view('budgets.index', [
            'budgets' => $budgets,
        ]);
    }

    public function create(Request $request): View
    {
        return view('budgets.form', [
            'budget' => new Budget([
                'period_type' => BudgetPeriod::Monthly,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
            ]),
            'categories' => Category::query()->availableFor($request->user())->active()->where('type', 'expense')->orderBy('name')->get(),
            'periodTypes' => BudgetPeriod::options(),
            'periodValue' => now()->format('Y-m'),
        ]);
    }

    public function store(BudgetRequest $request): RedirectResponse
    {
        [$start, $end] = $request->periodDates();

        $request->user()->budgets()->create([
            'category_id' => $request->validated('category_id'),
            'period_type' => $request->validated('period_type'),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'amount' => $request->validated('amount'),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil dibuat.');
    }

    public function edit(Request $request, Budget $budget): View
    {
        $this->authorize('update', $budget);

        return view('budgets.form', [
            'budget' => $budget,
            'categories' => Category::query()->availableFor($request->user())->active()->where('type', 'expense')->orderBy('name')->get(),
            'periodTypes' => BudgetPeriod::options(),
            'periodValue' => $budget->period_type === BudgetPeriod::Monthly
                ? $budget->period_start->format('Y-m')
                : $budget->period_start->format('Y'),
        ]);
    }

    public function update(BudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);
        [$start, $end] = $request->periodDates();

        $budget->update([
            'category_id' => $request->validated('category_id'),
            'period_type' => $request->validated('period_type'),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'amount' => $request->validated('amount'),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil dihapus.');
    }
}
