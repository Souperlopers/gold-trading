<?php

namespace Tests\Unit;

use App\Helpers\SanitizeHelper;
use PHPUnit\Framework\TestCase;

class SanitizePhoneTest extends TestCase
{
    public function test_basic_iranian_phone_number_remains_unchanged()
    {
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('09123456789'));
    }

    public function test_strips_spaces_and_dots()
    {
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('0912 345 67 89'));
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('0912.345.67.89'));
    }

    public function test_persian_and_arabic_digits_are_converted()
    {
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('۰۹۱۲۳۴۵۶۷۸۹')); // Persian
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('٠٩١٢٣٤٥٦٧٨٩')); // Arabic
    }

    public function test_prepends_zero_when_missing()
    {
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('9123456789'));
    }

    public function test_removes_98_prefix()
    {
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('989123456789'));     // 98 prefix
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('989123456789'));     // same
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('0989123456789'));    // 098 prefix
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('00989123456789'));   // 0098 prefix
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('+989123456789'));    // +98 prefix
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('+98 9123456789'));   // +98 with space
    }

    public function test_handles_dot_prefix()
    {
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('.9123456789'));
    }

    public function test_what_invalid_input_returns()
    {
        $this->assertEmpty(SanitizeHelper::sanitizePhone(null));
        $this->assertEmpty(SanitizeHelper::sanitizePhone(''));
        $this->assertEquals('12345', SanitizeHelper::sanitizePhone('12345'));       // too short
        $this->assertEquals('091234567890', SanitizeHelper::sanitizePhone('091234567890')); // too long
        $this->assertEquals('abcdefghijk', SanitizeHelper::sanitizePhone('abcdefghijk'));  // non-numeric
    }

    public function test_sanitization_with_mixed_characters()
    {
        $this->assertEquals('09123456789', SanitizeHelper::sanitizePhone('+98 ۹۱۲-۳۴۵-۶۷۸۹'));
    }
}
