<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin
        User::factory()->create([
            'name' => 'Admin POS',
            'email' => 'admin@pos.com',
        ]);

        // 2. Buat Toko Utama
        $storeMarkas = Store::create([
            'name' => 'Markasnack',
            'slug' => 'markasnack',
            'description' => 'Toko cemilan prasmanan & gramasi',
        ]);

        $storeGlytch = Store::create([
            'name' => 'Glytch',
            'slug' => 'glytch',
            'description' => 'Toko aksesoris, gelang, & fashion',
        ]);

        // 3. Buat Kategori Per Toko
        $catRegular = Category::create(['store_id' => $storeMarkas->id, 'name' => 'Regular']);
        $catPremium = Category::create(['store_id' => $storeMarkas->id, 'name' => 'Premium']);

        $catCincin  = Category::create(['store_id' => $storeGlytch->id, 'name' => 'Cincin']);
        $catKalung  = Category::create(['store_id' => $storeGlytch->id, 'name' => 'Kalung']);
        $catGelang  = Category::create(['store_id' => $storeGlytch->id, 'name' => 'Gelang']);
        Category::create(['store_id' => $storeGlytch->id, 'name' => 'Keychain']);

        // 4. Tier Harga Markasnack (Regular & Premium)
        $regularTiers = [
            ['min_gram' => 50, 'tier_price' => 6000],
            ['min_gram' => 100, 'tier_price' => 10000],
            ['min_gram' => 200, 'tier_price' => 20000],
            ['min_gram' => 500, 'tier_price' => 40000],
        ];
        foreach ($regularTiers as $tier) {
            PriceTier::create(array_merge($tier, ['group_type' => 'regular']));
        }

        // 5. Data Supplier UMKM
        $supplierUmkm = Supplier::create([
            'name' => 'Kripik Ibu Astuti (UMKM)',
            'phone' => '08123456789',
            'address' => 'Banjarbaru',
        ]);

        // 6. Produk & Batch Stok (Markasnack)
        $p1 = Product::create([
            'store_id' => $storeMarkas->id,
            'category_id' => $catRegular->id,
            'name' => 'Keripik Singkong Balado',
            'unit_type' => 'gram',
        ]);
        ProductBatch::create([
            'product_id' => $p1->id,
            'batch_code' => 'BATCH-001',
            'buy_price' => 45000, // Modal per kg
            'sell_price' => 0,    // Mengikuti PriceTier
            'initial_stock' => 10000,
            'current_stock' => 10000,
        ]);

        // 7. Produk Titipan UMKM (Konsinyasi)
        $p2 = Product::create([
            'store_id' => $storeMarkas->id,
            'category_id' => $catRegular->id,
            'supplier_id' => $supplierUmkm->id,
            'name' => 'Keripik Pisang Lumer (UMKM)',
            'unit_type' => 'pcs',
            'is_consignment' => true,
        ]);
        ProductBatch::create([
            'product_id' => $p2->id,
            'batch_code' => 'BATCH-UMKM-01',
            'buy_price' => 10000, // Setoran ke UMKM
            'sell_price' => 13000, // Harga Jual
            'initial_stock' => 20,
            'current_stock' => 20,
        ]);

        // 8. Produk Glytch (Retail Pcs)
        $p3 = Product::create([
            'store_id' => $storeGlytch->id,
            'category_id' => $catCincin->id,
            'name' => 'Cincin Titanium Dark Cyber',
            'unit_type' => 'pcs',
        ]);
        ProductBatch::create([
            'product_id' => $p3->id,
            'batch_code' => 'BATCH-GLY-01',
            'buy_price' => 15000, // HPP Batch 1
            'sell_price' => 45000,
            'initial_stock' => 15,
            'current_stock' => 15,
        ]);
    }
}
