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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_no');
            $table->string('item_name');

            $table->string('category')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('sn')->unique();
            $table->string('warehouse')->nullable();
            $table->date('tanggal_real')->nullable();
            $table->date('tanggal_fake')->nullable();
            $table->string('so_id')->nullable();
            $table->string('type_garansi')->nullable();
            $table->string('gambar')->nullable();

            $table->integer('qty')->default(0);

            $table->string('status')->default('unkeep');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_items');
    }
};
