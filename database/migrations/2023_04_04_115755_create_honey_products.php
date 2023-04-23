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
        Schema::create('honey_products', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('sku')->nullable();
            $table->string('first_var_id')->nullable();
            $table->string('inv_item_id')->nullable();
            $table->bigInteger('inv_int_id')->nullable();
            $table->integer('stock')->nullable();
            $table->string('shopify_id')->nullable();
            $table->bigInteger('intID')->nullable();
            $table->string('barcode')->nullable();
            $table->enum('newProduct', ['yes', 'no'])->default('no');
            $table->timestamp('synced_at')->nullable();
            $table->enum('imagesSynced', ['yes', 'no'])->default('no');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honey_products');
    }
};
