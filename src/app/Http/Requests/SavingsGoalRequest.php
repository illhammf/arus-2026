<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavingsGoalRequest extends FormRequest
{
    use NormalizesMoneyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMoneyFields(['target_amount']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'target_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'target_date' => ['nullable', 'date', $this->route('goal') ? 'sometimes' : 'after_or_equal:today'],
            'linked_account_id' => ['nullable', 'integer', Rule::exists('financial_accounts', 'id')->where(function ($query) {
                $query->where('user_id', $this->user()->id);
            })],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama target wajib diisi.',
            'target_amount.required' => 'Nominal target wajib diisi.',
            'target_amount.min' => 'Nominal target harus lebih besar dari nol.',
            'target_date.after_or_equal' => 'Tanggal target tidak boleh di masa lalu.',
            'linked_account_id.exists' => 'Akun terkait tidak valid.',
        ];
    }
}
