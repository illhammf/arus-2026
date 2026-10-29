<?php

namespace App\Http\Requests;

use App\Enums\BudgetPeriod;
use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BudgetRequest extends FormRequest
{
    use NormalizesMoneyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMoneyFields(['amount']);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('user_id')->orWhere('user_id', $this->user()->id);
                })->where('is_active', true)->where('type', 'expense');
            })],
            'period_type' => ['required', Rule::enum(BudgetPeriod::class)],
            'period' => ['required', 'string', 'max:7'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $period = (string) $this->input('period');
                $type = $this->input('period_type');

                if ($type === BudgetPeriod::Monthly->value) {
                    $valid = (bool) preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $period);
                } else {
                    $valid = (bool) preg_match('/^\d{4}$/', $period)
                        && (int) $period >= 2000
                        && (int) $period <= 2100;
                }

                if (! $valid) {
                    $validator->errors()->add('period', 'Format periode tidak valid.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Kategori anggaran tidak valid.',
            'period_type.required' => 'Jenis periode wajib dipilih.',
            'period.required' => 'Periode wajib diisi.',
            'amount.required' => 'Nominal anggaran wajib diisi.',
            'amount.min' => 'Nominal anggaran harus lebih besar dari nol.',
        ];
    }

    public function periodDates(): array
    {
        $type = BudgetPeriod::from((string) $this->input('period_type'));
        $period = (string) $this->input('period');

        if ($type === BudgetPeriod::Monthly) {
            $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } else {
            $start = Carbon::create((int) $period, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
        }

        return [$start, $end];
    }
}
