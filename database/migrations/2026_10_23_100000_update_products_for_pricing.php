<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Satuan: dari enum (pcs, gram, bucket) jadi string agar bisa pcs, gram, ml
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit_type', 10)->default('pcs')->change();
        });

        // Data lama bertipe "bucket" dipindah ke "ml"
        DB::table('products')->where('unit_type', 'bucket')->update(['unit_type' => 'ml']);

        // 2. Harga jual satuan (khusus produk pcs)
        if (!Schema::hasColumn('products', 'sell_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('sell_price', 12, 2)->default(0)->after('unit_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'sell_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('sell_price');
            });
        }

        DB::table('products')->where('unit_type', 'ml')->update(['unit_type' => 'bucket']);
        DB::table('products')->whereNotIn('unit_type', ['pcs', 'gram', 'bucket'])->update(['unit_type' => 'pcs']);

        Schema::table('products', function (Blueprint $table) {
            $table->enum('unit_type', ['pcs', 'gram', 'bucket'])->default('pcs')->change();
        });
    }
};
