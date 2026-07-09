<?php

namespace App\Helpers;

use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

class SanitizeHelper
{
    /**
     * Convert Persian/Arabic digits to Western digits
     */
    private static function en(string $string): string
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        $pr = str_replace($persian, $western, $string);
        return str_replace($arabic, $western, $pr);
    }

    public static function sanitizePhone(?string $phone): string
    {
        // reject if there aren't any input
        if (!$phone) return '';

        // Convert all chars to en digits
        $phone = self::en(trim($phone));

        // .9158636712 => 09158636712
        if (strpos($phone, '.') === 0) {
            $phone = '0' . substr($phone, 1);
        }

        $phone = str_replace(['.', ' ', '-', '_'], '', $phone);

        // 00989185223232 => 9185223232
        if (strpos($phone, '0098') === 0) {
            $phone = substr($phone, 4);
        }
        // 0989108210911 => 9108210911
        if (strlen($phone) == 13 && strpos($phone, '098') === 0) {
            $phone = substr($phone, 3);
        }
        // +989151234567 => 9151234567
        if (strlen($phone) == 13 && strpos($phone, '+98') === 0) {
            $phone = substr($phone, 3);
        }
        // +98 9151234567 => 9151234567
        if (strlen($phone) == 14 && strpos($phone, '+98 ') === 0) {
            $phone = substr($phone, 4);
        }
        // 989151234567 => 9151234567
        if (strlen($phone) == 12 && strpos($phone, '98') === 0) {
            $phone = substr($phone, 2);
        }

        // Prepend 0
        if (strpos($phone, '0') !== 0 && strlen($phone) == 10) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    public static function sanitizeNationalCode(?string $code): string|bool
    {
        // reject if there aren't any input
        if (!$code) return '';
        
        // prepending zeros
        $code = match (strlen($code)) {
            8 => '00' . $code,
            9 => '0' . $code,
            default => $code,
        };

        // trim and remove redundant characters
        $code = str_replace(['.', ' ', '-', '_'], '', $code);
        $code = self::en($code);

        return $code;
    }
}
