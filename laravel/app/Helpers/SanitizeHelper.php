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
        $arabic  = [
            '٠',
            '١',
            '٢',
            '٣',
            '٤',
            '٥',
            '٦',
            '٧',
            '٨',
            '٩'
        ];

        $pr = str_replace($persian, $western, $string);
        return str_replace($arabic, $western, $pr);
    }

    /**
     * Sanitize Phone Numbers
     */
    public static function sanitizePhone(?string $phone): string
    {
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

    /**
     * Sanitize National Code
     */
    public static function sanitizeNationalCode(?string $code): string|bool
    {
        // reject if there aren't any input
        if (!$code) return false;

        // trim and remove redundant characters
        $code = str_replace(['.', ' ', '-', '_'], '', $code);
        $code = self::en($code);

        // reject if there are any characters except numbers
        if (!preg_match('/^[0-9]{8,10}$/', $code)) {
            return false;
        }

        // reject if all digits are same
        for ($i = 0; $i < 10; $i++) {
            if (preg_match('/^' . $i . '{10}$/', $code)) {
                return false;
            }
        }

        // prepending zeros
        if (($codeLen = strlen($code)) < 10) {
            $zeroNum = 10 - $codeLen;
            $code = str_repeat('0', $zeroNum) . $code;
        }

        // calculate checksum
        for ($i = 10, $sum = 0; $i > 0; $i--) {
            $digit = substr($code, $i - 10, 1);
            $sum += $i * intval($digit);
        }
        $ret = $sum % 11;

        // get pariti digit
        $parity = intval(substr($code, 9, 1));


        if (($ret < 2 && $ret == $parity) || ($ret >= 2 && $ret == 11 - $parity)) {
            return $code;
        }

        return false;
    }
}
