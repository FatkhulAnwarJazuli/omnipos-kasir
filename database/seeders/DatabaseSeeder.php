<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\Supplier;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori Utama (Markasnack & Glytch)
        $catRegular = Category::create(['name' => 'Markasnack (Regular)']);
        $catPremium = Category::create(['name' => 'Markasnack (Premium)']);
        $catCincin  = Category::create(['name' => 'Glytch - Cincin']);
        $catKalung  = Category::create(['name' => 'Glytch - Kalung']);
        $catGelang  = Category::create(['name' => 'Glytch - Gelang']);
        Category::create(['name' => 'Glytch - Keychain']);
        Category::create(['name' => 'Glytch - Piercing']);
        Category::create(['name' => 'Glytch - Bandana']);

        // 2. Tier Harga Markasnack (Regular)
        $regularTiers = [
            ['min_gram' => 50, 'tier_price' => 6000],
            ['min_gram' => 100, 'tier_price' => 10000],
            ['min_gram' => 200, 'tier_price' => 20000],
            ['min_gram' => 250, 'tier_price' => 22000],
            ['min_gram' => 500, 'tier_price' => 40000],
            ['min_gram' => 1000, 'tier_price' => 75000],
        ];
        foreach ($regularTiers as $tier) {
            PriceTier::create(array_merge($tier, ['group_type' => 'regular']));
        }

        // 3. Tier Harga Markasnack (Premium)
        $premiumTiers = [
            ['min_gram' => 50, 'tier_price' => 7000],
            ['min_gram' => 100, 'tier_price' => 13000],
            ['min_gram' => 200, 'tier_price' => 25000],
            ['min_gram' => 250, 'tier_price' => 30000],
            ['min_gram' => 500, 'tier_price' => 58000],
            ['min_gram' => 1000, 'tier_price' => 108000],
        ];
        foreach ($premiumTiers as $tier) {
            PriceTier::create(array_merge($tier, ['group_type' => 'premium']));
        }

        // 4. Data Supplier UMKM
        $supplierUmkm = Supplier::create([
            'name' => 'Kripik Ibu Astuti (UMKM)',
            'phone' => '08123456789',
            'address' => 'Banjarbaru',
        ]);

        // 5. Sample Produk Markasnack (Timbangan)
        Product::create([
            'category_id' => $catRegular->id,
            'name' => 'Keripik Singkong Balado',
            'unit_type' => 'gram',
            'buy_price' => 0,
            'sell_price' => 0,
            'stock' => 10000, // 10.000 gram (10 kg)
            'is_consignment' => false,
        ]);

        // 6. Sample Produk Markasnack Non-Timbangan (Kemasan)
        Product::create([
            'category_id' => $catRegular->id,
            'name' => 'Minuman Es Teh Solo',
            'unit_type' => 'pcs',
            'buy_price' => 1500,
            'sell_price' => 5000,
            'stock' => 50,
            'is_consignment' => false,
        ]);

        // 7. Sample Produk Titipan UMKM (Konsinyasi)
        Product::create([
            'category_id' => $catRegular->id,
            'supplier_id' => $supplierUmkm->id,
            'name' => 'Keripik Pisang Lumer (UMKM)',
            'unit_type' => 'pcs',
            'buy_price' => 10000, // Setoran bersih ke UMKM
            'sell_price' => 13000, // Harga jual toko
            'stock' => 20,
            'is_consignment' => true,
        ]);

        // 8. Sample Produk Glytch (Retail Pcs)
        Product::create([
            'category_id' => $catCincin->id,
            'name' => 'Cincin Titanium Dark Cyber',
            'unit_type' => 'pcs',
            'buy_price' => 15000,
            'sell_price' => 45000,
            'stock' => 15,
            'is_consignment' => false,
        ]);
    }
}