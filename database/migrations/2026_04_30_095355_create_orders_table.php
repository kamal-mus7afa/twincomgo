<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable();

            $table->string('status')->default('DRAFT');

            $table->string('customer_no');
            $table->string('branch_name')->nullable();

            $table->boolean('taxable')->default(false);
            $table->boolean('inclusive_tax')->default(false);
            $table->text('description')->nullable();

            $table->string('accurate_so_number')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
