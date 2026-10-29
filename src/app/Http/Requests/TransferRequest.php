<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    use NormalizesMoneyInput;

    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        if (! $transaction instanceof \App\Models\Transaction) {
            return true;
        }

        return $transaction->user_id === $this->user()?->id;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMoneyFields(['amount']);
    }

    public function rules(): array
    {
        $ownedAccount = Rule::exists('financial_accounts', 'id')->where(function ($query) {
            $query->where('user_id', $this->user()->id)
                ->where('is_archived', false);
        });

        return [
            'source_account_id' => ['required', 'integer', $ownedAccount],
            'destination_account_id' => ['required', 'integer', $ownedAccount, 'different:source_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'occurred_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_account_id.required' => 'Akun sumber wajib dipilih.',
            'destination_account_id.required' => 'Akun tujuan wajib dipilih.',
            'destination_account_id.different' => 'Akun sumber dan tujuan harus berbeda.',
            'amount.required' => 'Nominal transfer wajib diisi.',
            'amount.min' => 'Nominal transfer harus lebih besar dari nol.',
            'occurred_at.required' => 'Tanggal transfer wajib diisi.',
        ];
    }
}
