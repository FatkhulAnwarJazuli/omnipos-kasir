<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // total_amount tetap = total akhir yang dibayar (setelah diskon)
            $table->decimal('subtotal_amount', 15, 2)->default(0)->after('invoice_number'); // sebelum diskon
            $table->foreignId('discount_id')->nullable()->after('total_amount')->constrained()->nullOnDelete();
            $table->string('discount_name')->nullable()->after('discount_id');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_name');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            // potongan yang dibagi proporsional ke tiap barang (agar laba per toko akurat)
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
        });

        // Transaksi lama: tidak ada diskon, jadi subtotal = total
        DB::table('transactions')->update(['subtotal_amount' => DB::raw('total_amount')]);
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn(['subtotal_amount', 'discount_name', 'discount_amount']);
        });
    }
};
