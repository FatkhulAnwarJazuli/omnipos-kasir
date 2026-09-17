<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori
        $catSnack = Category::create(['name' => 'Markasnack (Snack Prasmanan)']);
        $catAcc   = Category::create(['name' => 'Glytch (Aksesoris)']);

        // 2. Buat Produk Sampel
        $products = [
            // Markasnack
            Product::create(['category_id' => $catSnack->id, 'name' => 'Keripik Pisang Lumer (100g)', 'buy_price' => 8000, 'sell_price' => 12000, 'stock' => 100]),
            Product::create(['category_id' => $catSnack->id, 'name' => 'Basreng Pedas Jeruk (100g)', 'buy_price' => 7000, 'sell_price' => 10000, 'stock' => 150]),
            Product::create(['category_id' => $catSnack->id, 'name' => 'Makaroni Extra Pedas (100g)', 'buy_price' => 5000, 'sell_price' => 8000, 'stock' => 200]),

            // Glytch
            Product::create(['category_id' => $catAcc->id, 'name' => 'Gelang Manik Custom', 'buy_price' => 10000, 'sell_price' => 25000, 'stock' => 50]),
            Product::create(['category_id' => $catAcc->id, 'name' => 'Gantungan Kunci Y2K', 'buy_price' => 15000, 'sell_price' => 35000, 'stock' => 30]),
            Product::create(['category_id' => $catAcc->id, 'name' => 'Stiker Pack Retro', 'buy_price' => 5000, 'sell_price' => 15000, 'stock' => 80]),
        ];

        // 3. Generate 100 Transaksi Fiktif
        for ($i = 1; $i <= 100; $i++) {
            $randomDate = Carbon::now()->subDays(rand(1, 60))->subHours(rand(1, 12));

            $trx = Transaction::create([
                'transaction_code' => 'TRX-' . strtoupper(Str::random(6)),
                'total_amount'     => 0,
                'payment_method'   => rand(0, 1) ? 'cash' : 'qris',
                'created_at'       => $randomDate,
                'updated_at'       => $randomDate,
            ]);

            $totalAmount = 0;
            $itemCount = rand(1, 3);

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products[array_rand($products)];
                $qty     = rand(1, 4);
                $subtotal= $product->sell_price * $qty;
                $totalAmount += $subtotal;

                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'subtotal'       => $subtotal,
                    'created_at'     => $randomDate,
                    'updated_at'     => $randomDate,
                ]);
            }

            $trx->update(['total_amount' => $totalAmount]);
        }
    }
}
