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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('batch_code')->nullable(); // Nomor nota/batch
            $table->decimal('buy_price', 12, 2); // HPP khusus batch ini
            $table->decimal('sell_price', 12, 2); // Harga jual
            $table->decimal('initial_stock', 10, 2);
            $table->decimal('current_stock', 10, 2);
            $table->timestamp('purchased_at')->useCurrent();
            $table->date('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
