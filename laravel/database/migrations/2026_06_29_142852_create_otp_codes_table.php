<?php
// database/migrations/xxxx_xx_xx_create_otp_codes_table.php

use App\Models\OtpCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('code');
            $table->enum('purpose', array_keys(OtpCode::PURPOSE));
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('verification_token')->nullable();
            $table->mediumInteger('service_response')->nullable();
            $table->timestamp('token_used_at')->nullable();

            $table->index(['phone', 'purpose', 'token_used_at']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
