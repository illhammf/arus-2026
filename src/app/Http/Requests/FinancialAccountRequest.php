<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialAccountRequest extends FormRequest
{
    use NormalizesMoneyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMoneyFields(['opening_balance']);

        $this->merge([
            'is_archived' => $this->boolean('is_archived'),
            'currency' => strtoupper((string) $this->input('currency', 'IDR')),
        ]);
    }

    public function rules(): array
    {
        $accountId = $this->route('account')?->id;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('financial_accounts', 'name')
                    ->where(fn ($query) => $query->where('user_id', $this->user()->id))
                    ->ignore($accountId),
            ],
            'type' => ['required', Rule::enum(AccountType::class)],
            'currency' => ['required', 'string', 'size:3', 'in:IDR'],
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'opening_balance_date' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama akun wajib diisi.',
            'name.unique' => 'Nama akun sudah digunakan.',
            'type.required' => 'Jenis akun wajib dipilih.',
            'opening_balance.required' => 'Saldo awal wajib diisi.',
            'opening_balance.min' => 'Saldo awal tidak boleh negatif.',
        ];
    }
}
