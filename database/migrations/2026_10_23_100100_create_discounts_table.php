<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                   // contoh: Promo Akhir Bulan
            $table->enum('type', ['percent', 'nominal']);             // persen atau potongan rupiah
            $table->decimal('value', 12, 2);                          // 10 (= 10%) atau 5000 (= Rp5.000)
            $table->enum('scope', ['all', 'store', 'product'])->default('all');
            $table->foreignId('store_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('start_date')->nullable();                   // kosong = langsung berlaku
            $table->date('end_date')->nullable();                     // kosong = tanpa batas
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
