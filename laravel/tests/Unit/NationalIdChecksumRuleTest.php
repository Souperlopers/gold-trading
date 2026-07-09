<?php

namespace Tests\Unit;

use App\Models\OtpCode;
use App\Models\User;
use App\Rules\NationalIdChecksumRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class NationalIdChecksumRuleTest extends TestCase
{
    private NationalIdChecksumRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new NationalIdChecksumRule();
    }

    // ==================== VALID NATIONAL IDs ====================

    public function test_valid_national_ids_pass_validation()
    {
        // These are valid Iranian National IDs based on the checksum algorithm
        $validIds = ['42021332', '042021332', '0042021332', '9760068354', '6024878397', '0225163675', '9555878455',];

        foreach ($validIds as $id) {
            $validator = Validator::make(
                ['national_id' => $id],
                ['national_id' => $this->rule]
            );

            $this->assertTrue(
                $validator->passes(),
                "National ID '{$id}' should be valid but failed."
            );
        }
    }

    // ==================== INVALID NATIONAL IDs ====================

    public function test_all_same_digits_are_rejected()
    {
        $invalidIds = ['0000000000', '1111111111', '2222222222', '3333333333', '4444444444', '5555555555', '6666666666', '7777777777', '8888888888', '9999999999',];

        foreach ($invalidIds as $id) {
            $validator = Validator::make(
                ['national_id' => $id],
                ['national_id' => $this->rule]
            );

            $this->assertFalse(
                $validator->passes(),
                "National ID '{$id}' should be rejected (all same digits)."
            );

            $this->assertEquals(
                str_replace(':attribute', 'national ID', Lang::get('validation.custom.national_id.invalid_structure')),
                $validator->errors()->first('national_id')
            );
        }
    }

    public function test_invalid_checksum_is_rejected()
    {
        $invalidIds = ['9876543211', '1111111112', '0000000001',];

        foreach ($invalidIds as $id) {
            $validator = Validator::make(
                ['national_id' => $id],
                ['national_id' => $this->rule]
            );

            $this->assertFalse(
                $validator->passes(),
                "National ID '{$id}' should be rejected (invalid checksum)."
            );

            $this->assertEquals(
                str_replace(':attribute', 'national ID', Lang::get('validation.custom.national_id.invalid_structure')),
                $validator->errors()->first('national_id')
            );
        }
    }

    public function test_national_ids_with_letters_are_rejected()
    {
        $validator = Validator::make(
            ['national_id' => '123456789a'],
            ['national_id' => $this->rule]
        );

        $this->assertFalse($validator->passes());
        // Note: The rule doesn't explicitly reject letters, but the size:10 rule will catch this
        // if used in combination. If testing the rule alone, it will treat 'a' as 0.
        // This is why we use it with size:10 in the actual validation.
    }

    public function test_national_ids_with_special_characters_are_rejected()
    {
        $validator = Validator::make(
            ['national_id' => '1234-6789-0'],
            ['national_id' => $this->rule]
        );

        $this->assertFalse($validator->passes());
    }

    // ==================== ERROR MESSAGE TESTS ====================

    public function test_error_message_is_correct()
    {
        $validator = Validator::make(
            ['national_id' => '1111111111'],
            ['national_id' => $this->rule]
        );

        $this->assertFalse($validator->passes());
        $this->assertEquals(
            str_replace(':attribute', 'national ID', Lang::get('validation.custom.national_id.invalid_structure')),
            $validator->errors()->first('national_id')
        );
    }

    // ==================== EDGE CASES ====================

    public function test_national_id_with_length_less_than_10_is_rejected()
    {
        $validator = Validator::make(
            ['national_id' => '123456789'],
            ['national_id' => ['size:10', $this->rule]]
        );

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('national_id'));
    }

    public function test_national_id_with_length_greater_than_10_is_rejected()
    {
        $validator = Validator::make(
            ['national_id' => '12345678901'],
            ['national_id' => ['size:10', $this->rule]]
        );

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('national_id'));
    }

    public function test_rule_works_with_different_field_names()
    {
        $validator = Validator::make(
            ['code' => '1111111111'],
            ['code' => $this->rule]
        );

        $this->assertFalse($validator->passes());
        $this->assertEquals(
            str_replace(':attribute', 'code', Lang::get('validation.custom.national_id.invalid_structure')),
            $validator->errors()->first('code')
        );
    }

    public function test_rule_works_multiple_times_on_same_data()
    {
        $validator1 = Validator::make(
            ['national_id' => '9760068354'],
            ['national_id' => $this->rule]
        );
        $this->assertTrue($validator1->passes());

        $validator2 = Validator::make(
            ['national_id' => '1111111111'],
            ['national_id' => $this->rule]
        );
        $this->assertFalse($validator2->passes());
    }
}
