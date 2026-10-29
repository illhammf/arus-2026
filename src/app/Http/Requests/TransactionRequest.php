<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    use NormalizesMoneyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMoneyFields(['amount']);

        $this->merge([
            'type' => $this->input('type', TransactionType::Expense->value),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([
                TransactionType::Income->value,
                TransactionType::Expense->value,
            ])],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('financial_accounts', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id)
                        ->where('is_archived', false);
                }),
            ],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('user_id')->orWhere('user_id', $this->user()->id);
                })->where('is_active', true);
            })],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'occurred_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis transaksi wajib dipilih.',
            'account_id.required' => 'Akun wajib dipilih.',
            'account_id.exists' => 'Akun tidak valid atau sudah diarsipkan.',
            'category_id.exists' => 'Kategori tidak valid.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.min' => 'Nominal harus lebih besar dari nol.',
            'occurred_at.required' => 'Tanggal transaksi wajib diisi.',
        ];
    }
}
