<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'price_templates')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('price_templates')->nullable()->after('unit_type');
            });
        }

        if (!Schema::hasTable('stock_opnames')) {
            Schema::create('stock_opnames', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->double('system_stock');
                $table->double('physical_stock');
                $table->double('difference');
                $table->string('reason');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_templates');
        });
        Schema::dropIfExists('stock_opnames');
    }
};