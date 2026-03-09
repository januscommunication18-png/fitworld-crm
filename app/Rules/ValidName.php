<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Allows letters (including accented), spaces, hyphens, and apostrophes.
     * No numbers or special characters allowed.
     * Does not allow whitespace-only values.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Trim and check if it's only whitespace
        $trimmed = trim($value);
        if (empty($trimmed)) {
            $fail('The :attribute cannot be empty or contain only spaces.');
            return;
        }

        // Allow letters (including Unicode letters like é, ñ, ü), spaces, hyphens, apostrophes
        // Pattern: Only letters, spaces, hyphens, and apostrophes
        if (!preg_match("/^[\p{L}\s\-']+$/u", $trimmed)) {
            $fail('The :attribute must only contain letters, spaces, hyphens, and apostrophes.');
        }
    }
}
