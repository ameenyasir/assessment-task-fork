<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('merchant_id');
            // TODO: Replace me with a brief explanation of why floats aren't the correct data type, and replace with the correct data type.
            // Due to potential accuracy and rounding challenges, floating-point numbers are not suitable for accurately representing monetary values like commission rates or discounts.
            // Floats utilize binary representation for numbers, which can lead to inaccuracies during calculations involving decimal values.
            // For accurate representation of monetary amounts, it is recommended to opt for decimal data types over floats.
            // In the context of Laravel, you can employ the decimal method to define such columns in your migration file.
            $table->decimal('commission_rate', 2, 2);
            $table->string('discount_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliates');
    }
};
