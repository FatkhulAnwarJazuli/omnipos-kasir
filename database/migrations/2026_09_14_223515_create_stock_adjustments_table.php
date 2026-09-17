<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('system_stock', 12, 2);
            $table->decimal('actual_stock', 12, 2);
            $table->decimal('difference', 12, 2);
            $table->string('reason')->nullable();
            $table->timestamp('adjusted_at');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_adjustments');
    }
};
