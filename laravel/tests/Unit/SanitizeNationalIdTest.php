<?php

namespace Tests\Unit;

use App\Helpers\SanitizeHelper;
use PHPUnit\Framework\TestCase;

class SanitizeNationalIdTest extends TestCase
{
    public function test_correct_code_returns_unchaanged()
    {
        $this->assertEquals('0052011739', SanitizeHelper::sanitizeNationalCode('0052011739')); // 7 digits
    }

    public function test_too_long_code_returns_unchaanged()
    {
        $this->assertEquals('00052011739', SanitizeHelper::sanitizeNationalCode('00052011739')); // 7 digits
    }

    public function test_removes_dashes_spaces_and_underscores()
    {
        // Use a valid code with separators
        $this->assertEquals('0052011739', SanitizeHelper::sanitizeNationalCode('005-201-1739'));
        $this->assertEquals('0052011739', SanitizeHelper::sanitizeNationalCode('005 201 1739'));
        $this->assertEquals('0052011739', SanitizeHelper::sanitizeNationalCode('005_2011_739'));
    }

    public function test_converts_persian_and_arabic_digits()
    {
        // Persian digits
        $this->assertEquals('0012345678', SanitizeHelper::sanitizeNationalCode('۰۰۱۲۳۴۵۶۷۸'));
        // Arabic digits
        $this->assertEquals('0012345678', SanitizeHelper::sanitizeNationalCode('٠٠١٢٣٤٥٦٧٨'));
    }

    public function test_null_or_empty_input_returns_empty()
    {
        $this->assertEquals('', SanitizeHelper::sanitizeNationalCode(null));
        $this->assertEquals('', SanitizeHelper::sanitizeNationalCode(''));
    }
}
