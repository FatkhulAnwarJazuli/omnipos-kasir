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
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained()->onDelete('cascade');
        $table->string('title'); // Contoh: Sewa Tenda Event Bazaar, Transportasi, Konsumsi
        $table->enum('category', ['tenda', 'transportasi', 'operasional', 'konsumsi', 'lainnya']);
        $table->decimal('amount', 15, 2);
        $table->date('expense_date');
        $table->text('note')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
