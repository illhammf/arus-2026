<?php

namespace App\Http\Requests\Concerns;

trait NormalizesMoneyInput
{
    /**
     * Accept both "1.000.000" (Indonesian display format) and "1000000.50".
     */
    protected function normalizeMoneyFields(array $fields): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            $value = $this->input($field);

            if (is_string($value) && trim($value) !== '') {
                $value = preg_replace('/\s+/', '', trim($value));

                if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
                    $value = str_replace('.', '', $value);
                } elseif (str_contains($value, ',')) {
                    $value = str_replace('.', '', $value);
                    $value = str_replace(',', '.', $value);
                }
            }

            $normalized[$field] = $value;
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
