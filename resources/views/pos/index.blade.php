<x-app-layout>
    <!-- AlpineJS State dengan Filter Brand & Theme Sync -->
    <div x-data="posSystem()"
         x-init="initPos('{{ request('brand', 'all') }}')"
         class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- KATALOG PRODUK VISUAL -->
        <div class="lg:col-span-2 space-y-4">

            <!-- HEADER FILTER BRAND (MARKASNACK VS GLYTCH) -->
            <div class="flex items-center justify-between bg-white p-3 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center space-x-2">
                    <button @click="setBrandFilter('all')"
                            :class="activeBrand === 'all' ? 'bg-gray-900 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-xl text-xs transition">
                        🌐 Semua Store
                    </button>
                    <button @click="setBrandFilter('markasnack')"
                            :class="activeBrand === 'markasnack' ? 'bg-red-950 text-red-100 border border-red-800 font-bold' : 'bg-red-50 text-red-900 hover:bg-red-100'"
                            class="px-4 py-2 rounded-xl text-xs transition flex items-center space-x-1.5">
                        <span>🍿</span>
                        <span>Markasnack</span>
                    </button>
                    <button @click="setBrandFilter('glytch')"
                            :class="activeBrand === 'glytch' ? 'bg-black text-white font-bold' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-xl text-xs transition flex items-center space-x-1.5">
                        <span>💍</span>
                        <span>Glytch</span>
                    </button>
                </div>
            </div>

            <!-- Filter Sub-Kategori Spesifik -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-1 scrollbar-none">
                <button @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all' ? 'bg-amber-500 text-white font-bold' : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-3 py-1.5 rounded-xl text-xs transition shadow-sm whitespace-nowrap">
                    Semua Kategori
                </button>
                @foreach($categories as $cat)
                <button x-show="isCategoryMatch('{{ strtolower($cat->name) }}')"
                        @click="selectedCategory = '{{ $cat->id }}'"
                        :class="selectedCategory === '{{ $cat->id }}' ? 'bg-amber-500 text-white font-bold' : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-3 py-1.5 rounded-xl text-xs transition shadow-sm whitespace-nowrap">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>

            <!-- Grid Card Produk Visual (Auto Filter Brand) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @forelse($products as $product)
                <div x-show="shouldShowProduct({{ json_encode($product) }})"
                     @click="openProductModal({{ json_encode($product) }})"
                     class="bg-white rounded-2xl p-3 border border-gray-100 shadow-sm hover:shadow-md transition cursor-pointer group flex flex-col justify-between">

                    <div>
                        <div class="w-full h-32 bg-gray-100 rounded-xl overflow-hidden mb-3 relative flex items-center justify-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <span class="text-3xl">
                                    {{ $product->unit_type === 'gram' ? '🍿' : ($product->unit_type === 'bucket' ? '🪣' : '💍') }}
                                </span>
                            @endif

                            <span class="absolute top-2 right-2 text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $product->unit_type === 'gram' ? 'bg-red-100 text-red-700' : 'bg-neutral-800 text-white' }}">
                                {{ strtoupper($product->unit_type) }}
                            </span>
                        </div>

                        <h3 class="font-bold text-sm text-gray-800 line-clamp-1 group-hover:text-amber-600 transition">{{ $product->name }}</h3>
                        <p class="text-[11px] text-gray-400 mb-2">{{ $product->category->name ?? '-' }}</p>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-50">
                        <span class="font-black text-sm text-gray-900">
                            @if($product->unit_type !== 'pcs')
                                Tier System
                            @else
                                Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                            @endif
                        </span>
                        <span class="w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center text-xs font-bold transition">+</span>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-12 text-center text-gray-400">
                    Belum ada produk.
                </div>
                @endforelse
            </div>
        </div>

        <!-- UNIFIED CART PANEL -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between h-[calc(100vh-140px)] sticky top-24">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="font-bold text-lg text-gray-800">Keranjang Belanja</h3>
                    <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full font-bold" x-text="cart.length + ' Item'"></span>
                </div>

                <div class="mt-4 space-y-3 overflow-y-auto max-h-[300px] pr-1">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between border border-gray-100">
                            <div class="truncate pr-2">
                                <h4 class="font-bold text-xs text-gray-800 truncate" x-text="item.name"></h4>
                                <p class="text-[10px] text-gray-400" x-text="item.detail_info"></p>
                                <span class="font-bold text-xs text-gray-900" x-text="'Rp ' + item.subtotal.toLocaleString('id-ID')"></span>
                            </div>
                            <button @click="removeFromCart(index)" class="text-gray-400 hover:text-red-500 p-1">✕</button>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="py-12 text-center text-gray-400 text-xs">
                        Keranjang masih kosong.
                    </div>
                </div>
            </div>

            <!-- Total & Metode Bayar -->
            <div class="pt-4 border-t border-gray-100 space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-600 mb-1 block">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-amber-500 text-white font-bold' : 'bg-gray-100 text-gray-700'"
                                class="py-2 rounded-xl text-xs transition">
                            💵 Cash
                        </button>
                        <button @click="paymentMethod = 'qris'"
                                :class="paymentMethod === 'qris' ? 'bg-amber-500 text-white font-bold' : 'bg-gray-100 text-gray-700'"
                                class="py-2 rounded-xl text-xs transition">
                            📱 QRIS
                        </button>
                    </div>
                </div>

                <div class="flex justify-between items-center text-sm font-bold text-gray-800">
                    <span>Total Bayar</span>
                    <span class="text-xl font-black text-amber-600" x-text="'Rp ' + calculateTotal().toLocaleString('id-ID')"></span>
                </div>

                <button @click="processCheckout()" :disabled="cart.length === 0 || loading"
                        class="w-full py-3.5 bg-gray-900 hover:bg-black text-white rounded-xl font-bold text-sm shadow-md transition disabled:opacity-50">
                    <span x-show="!loading">Proses Pembayaran</span>
                    <span x-cloak x-show="loading">Memproses...</span>
                </button>
            </div>
        </div>

        <!-- MODAL POP-UP TIMBANGAN MARKASNACK -->
        <div x-cloak
             x-show="modalOpen"
             class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">

            <div @click.away="modalOpen = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="font-bold text-lg text-gray-900" x-text="activeProduct ? activeProduct.name : ''"></h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-700 block mb-1">Berat Timbangan (Gram)</label>
                    <input type="number" x-model.number="inputWeight" placeholder="Misal: 108"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 text-lg font-bold text-gray-900 focus:ring-amber-500">
                </div>

                <div x-show="activeProduct && activeProduct.unit_type === 'gram'">
                    <label class="text-xs font-bold text-gray-700 block mb-1">Pilih Preset Tier Harga</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="tier in getTiers()" :key="tier.id">
                            <button @click="selectedTier = tier"
                                    :class="selectedTier && selectedTier.id === tier.id ? 'bg-red-900 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="p-2 rounded-xl text-center text-xs transition">
                                <p class="font-extrabold" x-text="tier.min_gram + 'g'"></p>
                                <p class="text-[10px] opacity-80" x-text="'Rp ' + (tier.tier_price/1000) + 'k'"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <button @click="confirmAddGramProduct()" :disabled="!inputWeight || (activeProduct && activeProduct.unit_type === 'gram' && !selectedTier)"
                        class="w-full py-3 bg-red-900 hover:bg-red-950 text-white rounded-xl font-bold text-sm shadow-md transition disabled:opacity-50">
                    + Masukkan Keranjang
                </button>
            </div>
        </div> 

    </div>

    <!-- Script Logika Penyaringan Filter Brand & Kategori -->
    <script>
        function posSystem() {
            return {
                activeBrand: 'all',
                selectedCategory: 'all',
                cart: [],
                paymentMethod: 'cash',
                loading: false,
                modalOpen: false,
                activeProduct: null,
                inputWeight: null,
                selectedTier: null,
                regularTiers: @json($regularTiers),
                premiumTiers: @json($premiumTiers),

                initPos(brandParam) {
                    if (brandParam && brandParam !== 'all') {
                        // Cukup set state lokal, JANGAN panggil redirect agar tidak loop!
                        this.activeBrand = brandParam;
                    }
                },
                setBrandFilter(brand) {
                    // Redirect hanya ketika tombol filter diklik manual oleh user
                    window.location.href = '{{ route("pos.index") }}?brand=' + brand;
                },
                isCategoryMatch(catName) {
                    if(this.activeBrand === 'markasnack') return catName.includes('markasnack');
                    if(this.activeBrand === 'glytch') return catName.includes('glytch');
                    return true;
                },
                shouldShowProduct(product) {
                    const isGramOrBucket = product.unit_type === 'gram' || product.unit_type === 'bucket';
                    const isPcs = product.unit_type === 'pcs';

                    if(this.activeBrand === 'markasnack' && !isGramOrBucket) return false;
                    if(this.activeBrand === 'glytch' && !isPcs) return false;
                    if(this.selectedCategory !== 'all' && product.category_id != this.selectedCategory) return false;

                    return true;
                },
                openProductModal(product) {
                    if(product.unit_type === 'pcs') {
                        this.addToCartPcs(product);
                    } else {
                        this.activeProduct = product;
                        this.inputWeight = null;
                        this.selectedTier = null;
                        this.modalOpen = true;
                    }
                },
                getTiers() {
                    if(!this.activeProduct) return [];
                    return this.activeProduct.category_id == 2 ? this.premiumTiers : this.regularTiers;
                },
                addToCartPcs(product) {
                    let existing = this.cart.find(i => i.id === product.id);
                    if(existing) {
                        existing.qty += 1;
                        existing.subtotal = existing.qty * product.sell_price;
                        existing.detail_info = existing.qty + ' Pcs';
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            qty: 1,
                            subtotal: product.sell_price,
                            detail_info: '1 Pcs'
                        });
                    }
                },
                confirmAddGramProduct() {
                    let price = this.activeProduct.unit_type === 'bucket' ? 30000 : parseFloat(this.selectedTier.tier_price);
                    let tierName = this.activeProduct.unit_type === 'bucket' ? 'Bucket' : this.selectedTier.min_gram + 'g Tier';

                    this.cart.push({
                        id: this.activeProduct.id,
                        name: this.activeProduct.name,
                        qty: 1,
                        weight_actual: this.inputWeight,
                        tier_applied: tierName,
                        subtotal: price,
                        detail_info: tierName + ' (Aktual: ' + this.inputWeight + 'g)'
                    });

                    this.modalOpen = false;
                },
                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },
                calculateTotal() {
                    return this.cart.reduce((total, item) => total + item.subtotal, 0);
                },
                processCheckout() {
                    this.loading = true;
                    fetch('{{ route("pos.checkout") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cart: this.cart,
                            payment_method: this.paymentMethod
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        if(data.status === 'success') {
                            alert('Transaksi Berhasil!\nKode: ' + data.transaction_code);
                            this.cart = [];
                        } else {
                            alert('Terjadi kesalahan.');
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        alert('Gagal memproses transaksi.');
                    });
                }
            }
        }
    </script>
</x-app-layout>