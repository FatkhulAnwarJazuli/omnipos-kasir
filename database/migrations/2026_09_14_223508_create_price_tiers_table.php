<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->enum('group_type', ['regular', 'premium']);
            $table->integer('min_gram');
            $table->decimal('tier_price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('price_tiers');
    }
};
