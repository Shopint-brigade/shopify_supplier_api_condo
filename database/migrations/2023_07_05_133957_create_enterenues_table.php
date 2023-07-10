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
        Schema::create('enterenues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('upc'); // or sku
            $table->float('price', 8, 2);
            $table->integer('qty');
            $table->string('shopify_id');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterenues');
    }
};
