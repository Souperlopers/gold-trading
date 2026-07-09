<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NationalIdChecksumRule implements ValidationRule
{
    private $messagePath = 'validation.custom.national_id.invalid_structure';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // prepending zeros
        $value = match (strlen($value)) {
            8 => '00' . $value,
            9 => '0' . $value,
            default => $value,
        };

        // reject if all digits are same
        $charsAreSame = fn($str) => count(array_unique(str_split($str))) == 1;
        if ($charsAreSame($value)) {
            $fail(__($this->messagePath));
            return;
        }

        // Convert to array of digits
        $digits = str_split($value);

        // Calculate checksum for first 9 digits
        for ($sum = 0, $i = 0; $i < 9; $i++) {
            $pos = 10 - $i;
            $sum += $pos * intval($digits[$i]);
        }

        $remainder = $sum % 11;
        $parity = intval($digits[9]); // 10th digit

        if (
            ($remainder <  2 && $remainder !==     $parity) ||
            ($remainder >= 2 && $remainder !== (11 - $parity))
        ) {
            $fail(__($this->messagePath));
            return;
        }
    }
}
