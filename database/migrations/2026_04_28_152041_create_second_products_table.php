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
        Schema::create('second_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // SOURCE PI
            $table->string('purchase_invoice_number');
            $table->string('purchase_invoice_id')->nullable();

            // ITEM
            $table->string('item_id');
            $table->string('item_no');
            $table->string('item_name');

            // SERIAL NUMBER
            $table->string('serial_number')->unique()->nullable();

            $table->string('customer_id')->nullable();
            $table->string('customer_no')->nullable();
            $table->string('customer_name')->nullable();

            $table->string('sales_order_id')->nullable();
            $table->string('sales_order_number')->nullable();

            $table->enum('status', [
                'diajukan',
                'ready',
                'booked',
                'sold',
                'cancel'
            ])->default('diajukan');
            $table->enum('type_garansi', ['resmi', 'distributor'])->nullable();
            $table->date('tanggal_real')->nullable();
            $table->date('tanggal_fake')->nullable();

            $table->decimal('selling_price', 15, 2)
                ->nullable();

            $table->boolean('is_publish')
                ->default(false);

            $table->text('description')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('second_products');
    }
};
