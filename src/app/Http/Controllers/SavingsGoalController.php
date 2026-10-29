<?php

namespace App\Http\Controllers;

use App\Enums\GoalStatus;
use App\Http\Requests\SavingsContributionRequest;
use App\Http\Requests\SavingsGoalRequest;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsGoalController extends Controller
{
    public function index(Request $request): View
    {
        $goals = $request->user()->savingsGoals()
            ->withCount('contributions')
            ->orderBy('status')
            ->orderBy('target_date')
            ->get();

        return view('goals.index', ['goals' => $goals]);
    }

    public function create(Request $request): View
    {
        return view('goals.form', [
            'goal' => new SavingsGoal(['status' => GoalStatus::Active]),
            'accounts' => $request->user()->financialAccounts()->where('is_archived', false)->orderBy('name')->get(),
        ]);
    }

    public function store(SavingsGoalRequest $request): RedirectResponse
    {
        $goal = $request->user()->savingsGoals()->create([
            'name' => $request->validated('name'),
            'target_amount' => $request->validated('target_amount'),
            'target_date' => $request->validated('target_date'),
            'linked_account_id' => $request->validated('linked_account_id'),
            'notes' => $request->validated('notes'),
            'status' => GoalStatus::Active,
        ]);

        return redirect()->route('goals.show', $goal)->with('success', 'Target tabungan berhasil dibuat.');
    }

    public function show(SavingsGoal $goal): View
    {
        $this->authorize('view', $goal);
        $goal->load(['linkedAccount', 'contributions' => fn ($query) => $query->latest('contributed_at')]);

        return view('goals.show', [
            'goal' => $goal,
            'contributed' => $goal->contributedTotal(),
            'remaining' => $goal->remaining(),
            'percentage' => $goal->progressPercentage(),
        ]);
    }

    public function edit(Request $request, SavingsGoal $goal): View
    {
        $this->authorize('update', $goal);

        return view('goals.form', [
            'goal' => $goal,
            'accounts' => $request->user()->financialAccounts()->where('is_archived', false)->orderBy('name')->get(),
        ]);
    }

    public function update(SavingsGoalRequest $request, SavingsGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $goal->update($request->validated());

        return redirect()->route('goals.show', $goal)->with('success', 'Target tabungan berhasil diperbarui.');
    }

    public function contribute(SavingsContributionRequest $request, SavingsGoal $goal): RedirectResponse
    {
        $this->authorize('view', $goal);

        $goal->contributions()->create([
            'user_id' => $request->user()->id,
            'amount' => $request->validated('amount'),
            'contributed_at' => $request->validated('contributed_at'),
            'notes' => $request->validated('notes'),
        ]);

        if (Money::compare($goal->contributedTotal(), $goal->target_amount) >= 0) {
            $goal->update(['status' => GoalStatus::Completed]);
        }

        return back()->with('success', 'Kontribusi tabungan berhasil dicatat.');
    }

    public function destroyContribution(SavingsGoal $goal, SavingsContribution $contribution): RedirectResponse
    {
        $this->authorize('delete', $contribution);
        abort_unless($contribution->savings_goal_id === $goal->id && $contribution->user_id === request()->user()->id, 404);

        $contribution->delete();

        if ($goal->status === GoalStatus::Completed && Money::compare($goal->contributedTotal(), $goal->target_amount) < 0) {
            $goal->update(['status' => GoalStatus::Active]);
        }

        return back()->with('success', 'Kontribusi berhasil dihapus.');
    }

    public function destroy(SavingsGoal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);
        $goal->delete();

        return redirect()->route('goals.index')->with('success', 'Target tabungan berhasil dihapus.');
    }
}
