<?php

namespace Tests\Unit;

use App\Helpers\SanitizeHelper;
use PHPUnit\Framework\TestCase;

class SanitizeNationalIdTest extends TestCase
{
    public function test_too_short_code_prepends_with_zero()
    {
        $this->assertEquals('0042021332', SanitizeHelper::sanitizeNationalCode('42021332'));
        $this->assertEquals('0042021332', SanitizeHelper::sanitizeNationalCode('042021332'));
    }

    public function test_correct_code_returns_unchaanged()
    {
        $this->assertEquals('0052011739', SanitizeHelper::sanitizeNationalCode('0052011739'));
    }

    public function test_too_long_code_returns_unchaanged()
    {
        $this->assertEquals('00052011739', SanitizeHelper::sanitizeNationalCode('00052011739'));
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
