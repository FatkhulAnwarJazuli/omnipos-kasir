@extends('layouts.app')

@section('content')
<style>
    @media print {
        body * { 
            visibility: hidden; 
        }
        #receipt-print-area, #receipt-print-area * { 
            visibility: visible; 
            font-family: Arial, Helvetica, sans-serif !important;
            font-weight: normal !important; /* Memastikan font tidak terlalu tebal/polusi warna */
        }
        #receipt-print-area { 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
        }
    }
</style>

<div x-data="posSystem()" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start relative">

    <!-- TOAST NOTIFICATION CONTAINER -->
    <template x-teleport="body">
        <div x-show="toast.show" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
             class="fixed top-5 right-5 z-[10000] max-w-sm w-full">
            <div :class="{
                    'bg-slate-900 text-white border-emerald-500/30 shadow-emerald-500/10': toast.type === 'success',
                    'bg-slate-900 text-white border-rose-500/30 shadow-rose-500/10': toast.type === 'error',
                    'bg-slate-900 text-white border-amber-500/30 shadow-amber-500/10': toast.type === 'warning',
                    'bg-slate-900 text-white border-indigo-500/30 shadow-indigo-500/10': toast.type === 'info'
                 }"
                 class="flex items-center justify-between gap-3 px-4 py-3.5 rounded-2xl border shadow-2xl font-medium text-xs backdrop-blur-xl">
                <div class="flex items-center gap-3">
                    <span x-text="toast.type === 'success' ? '✅' : (toast.type === 'error' ? '❌' : (toast.type === 'info' ? 'ℹ️' : '⚠️'))" class="text-base"></span>
                    <span x-text="toast.message" class="text-slate-200 font-semibold leading-relaxed"></span>
                </div>
                <button @click="toast.show = false" class="text-slate-400 hover:text-white font-bold text-lg cursor-pointer ml-2">&times;</button>
            </div>
        </div>
    </template>

    <!-- Area Katalog Produk (Kiri - 7 Col) -->
    <div class="lg:col-span-7 space-y-4">
        <!-- Filter & Search Bar -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="text" x-model="searchQuery" placeholder="Cari nama produk..." class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">

                <select x-model="selectedStore" class="px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white">
                    <option value="">Semua Toko</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>

                <select x-model="selectedCategory" class="px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Grid Cards Produk -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="selectProduct(product)" 
                     :class="getTotalStock(product) <= 0 ? 'opacity-50 cursor-not-allowed' : 'hover:border-indigo-500 hover:shadow-md cursor-pointer'" 
                     class="bg-white border border-slate-200/80 rounded-2xl p-3 shadow-sm transition flex flex-col justify-between min-h-[210px] h-full overflow-hidden">
                    
                    <div class="space-y-1.5">
                        <!-- Image Container -->
                        <div class="w-full h-24 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center border border-slate-100/80 relative">
                            <template x-if="product.image">
                                <img :src="'/storage/' + product.image" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!product.image">
                                <span class="text-slate-400 font-bold text-xs" x-text="product.name.substring(0, 2).toUpperCase()"></span>
                            </template>
                            <span class="absolute top-1.5 left-1.5 rounded-md bg-white/90 backdrop-blur-md px-2 py-0.5 text-[9px] font-bold text-indigo-700 border border-slate-200/60 shadow-2xl" x-text="product.store ? product.store.name : '-'"></span>
                        </div>

                        <!-- Product Title -->
                        <h4 class="font-bold text-slate-800 text-xs leading-snug line-clamp-2" x-text="product.name"></h4>
                    </div>

                    <!-- Card Footer (Harga & Stok) -->
                    <div class="pt-2 mt-2 border-t border-slate-100 flex items-center justify-between gap-1">
                        <span class="text-[11px] font-extrabold text-indigo-600 truncate" x-text="priceLabel(product)"></span>
                        <span class="text-[9px] font-medium text-slate-400 whitespace-nowrap shrink-0" 
                              x-text="getTotalStock(product) <= 0 ? 'Habis' : 'Stok: ' + formatNumber(getTotalStock(product)) + ' ' + (product.unit_type === 'gram' ? 'gr' : product.unit_type)">
                        </span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Area Keranjang Belanja (Kanan - 5 Col) -->
    <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-4 sticky top-6">
        
        <!-- Status & Tombol Bluetooth Printer -->
        <div class="flex items-center justify-between bg-slate-50 border border-slate-200/80 p-2.5 rounded-xl text-xs">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" :class="btConnected ? 'bg-emerald-500 animate-pulse' : 'bg-rose-400'"></span>
                <span class="font-bold text-slate-700" x-text="btConnected ? 'RPP02N Terhubung' : 'Printer Terputus'"></span>
            </div>
            <button type="button" @click="connectBluetooth()" class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer border"
                    :class="btConnected ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' : 'bg-indigo-600 text-white hover:bg-indigo-700 border-indigo-600'">
                <span x-text="btConnected ? '🔄 Sambung Ulang' : '🔌 Konek Bluetooth'"></span>
            </button>
        </div>

        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-base">Keranjang Belanja</h3>
            <div class="flex items-center gap-2">
                <button @click="openVoidModal = true" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg border border-rose-200/80 transition cursor-pointer flex items-center gap-1">
                    <span>🚫</span> Void Struk
                </button>
                <button @click="cart = []" class="text-xs text-slate-400 hover:text-rose-500 hover:underline cursor-pointer">Kosongkan</button>
            </div>
        </div>

        <!-- List Items Cart -->
        <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
            <template x-for="item in cart" :key="item.key">
                <div class="flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div class="space-y-0.5">
                        <h5 class="font-bold text-slate-800 text-xs" x-text="item.name"></h5>
                        <p class="text-[10px] text-slate-400" x-text="item.store_name + ' • ' + portionLabel(item) + ' @ Rp ' + formatNumber(item.price)"></p>
                        <p class="text-[11px] font-bold text-indigo-600" x-text="'Rp ' + formatNumber(item.price * item.qty)"></p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="changeQty(item, -1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 font-bold cursor-pointer">&minus;</button>
                        <span class="w-6 text-center text-xs font-bold" x-text="item.qty"></span>
                        <button type="button" @click="changeQty(item, 1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 font-bold cursor-pointer">+</button>
                        <button type="button" @click="removeItem(item)" class="text-rose-400 hover:text-rose-600 font-bold text-base ml-1 cursor-pointer">&times;</button>
                    </div>
                </div>
            </template>
            <template x-if="cart.length === 0">
                <p class="text-center py-8 text-xs text-slate-400">Keranjang masih kosong.</p>
            </template>
        </div>

        <!-- Diskon -->
        <template x-if="discounts.length > 0">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Diskon</label>
                <select x-model="selectedDiscountId" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white">
                    <option value="">Tanpa diskon</option>
                    <template x-for="d in discounts" :key="d.id">
                        <option :value="d.id" x-text="d.name + ' (' + (d.type === 'percent' ? Number(d.value) + '%' : 'Rp ' + formatNumber(d.value)) + ')'"></option>
                    </template>
                </select>
                <template x-if="selectedDiscount && discountAmount === 0 && cart.length > 0">
                    <p class="text-[10px] text-amber-600 mt-1">Diskon ini tidak berlaku untuk isi keranjang.</p>
                </template>
            </div>
        </template>

        <!-- Total Summary -->
        <div class="border-t border-slate-100 pt-3 space-y-2">
            <template x-if="discountAmount > 0">
                <div class="space-y-1 text-xs text-slate-500">
                    <div class="flex justify-between"><span>Subtotal</span><span x-text="'Rp ' + formatNumber(subtotal)"></span></div>
                    <div class="flex justify-between text-emerald-600"><span>Diskon</span><span x-text="'- Rp ' + formatNumber(discountAmount)"></span></div>
                </div>
            </template>
            <div class="flex justify-between text-sm font-bold text-slate-900">
                <span>Total Belanja</span>
                <span class="text-indigo-600" x-text="'Rp ' + formatNumber(grandTotal)"></span>
            </div>
            <button @click="payAmount = 0; openPayModal = true" :disabled="cart.length === 0" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-indigo-500/20 cursor-pointer">
                Bayar Sekarang
            </button>
        </div>
    </div>

    <!-- MODAL PILIH PORSI -->
    <template x-teleport="body">
        <div x-show="openPortionModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            <div @click.away="openPortionModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base" x-text="selectedPortionProduct ? selectedPortionProduct.name : ''"></h3>
                    <p class="text-xs text-slate-400">Pilih porsi</p>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <template x-for="tpl in (selectedPortionProduct && selectedPortionProduct.price_templates) || []" :key="tpl.amount">
                        <button type="button" @click="addToCart(selectedPortionProduct, tpl.amount, tpl.price); openPortionModal = false" :disabled="!canAdd(selectedPortionProduct, tpl.amount)" class="p-3 border border-indigo-200 hover:bg-indigo-50 disabled:opacity-40 disabled:hover:bg-white rounded-xl text-center cursor-pointer">
                            <span class="block text-sm font-extrabold text-slate-900" x-text="formatNumber(tpl.amount) + ' ' + unitLabel(selectedPortionProduct)"></span>
                            <span class="block text-[11px] font-bold text-indigo-600" x-text="'Rp ' + formatNumber(tpl.price)"></span>
                        </button>
                    </template>
                </div>

                <div class="flex justify-end">
                    <button type="button" @click="openPortionModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                </div>
            </div>
        </div>
    </template>

    <!-- MODAL PEMBAYARAN -->
    <template x-teleport="body">
        <div x-show="openPayModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            <div @click.away="openPayModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Pembayaran</h3>
                <div class="p-3 bg-slate-50 rounded-xl space-y-1">
                    <template x-if="discountAmount > 0">
                        <p class="text-[11px] text-emerald-600" x-text="'Diskon ' + (selectedDiscount ? selectedDiscount.name : '') + ': - Rp ' + formatNumber(discountAmount)"></p>
                    </template>
                    <span class="text-xs text-slate-500">Total Tagihan</span>
                    <h2 class="text-2xl font-extrabold text-indigo-600" x-text="'Rp ' + formatNumber(grandTotal)"></h2>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                    <select x-model="paymentMethod" class="w-full px-3 py-2 border rounded-xl text-sm bg-white">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
                <template x-if="paymentMethod === 'cash'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Uang Diterima</label>
                            <div class="flex gap-2">
                                <input type="number" x-model.number="payAmount" class="w-full px-3 py-2 border rounded-xl text-sm font-bold">
                                <button type="button" @click="payAmount = grandTotal" class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 whitespace-nowrap cursor-pointer">Uang Pas</button>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl flex justify-between items-center text-sm">
                            <span class="font-medium text-slate-600">Kembalian</span>
                            <span class="font-bold text-emerald-600" x-text="'Rp ' + formatNumber(payAmount - grandTotal > 0 ? payAmount - grandTotal : 0)"></span>
                        </div>
                    </div>
                </template>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="openPayModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs cursor-pointer">Batal</button>
                    <button type="button" @click="submitCheckout()" :disabled="processing" class="px-5 py-2 bg-indigo-600 disabled:bg-slate-300 text-white font-bold rounded-xl text-xs shadow-md cursor-pointer" x-text="processing ? 'Memproses...' : 'Proses Checkout'"></button>
                </div>
            </div>
        </div>
    </template>

    <!-- MODAL STRUK / NOTA BELANJA (LAYOUT GAYA TINYBITES) -->
    <template x-teleport="body">
        <div x-show="openReceiptModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 sm:p-8 space-y-5 text-slate-800 my-auto border border-slate-100">

                <div id="receipt-print-area" class="space-y-5">
                    
                    <!-- Header Logo & Title Invoice -->
                    <div class="flex justify-between items-start border-b border-rose-100 pb-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <img src="/images/MARKASNACK.jpg" class="h-9 w-9 rounded-full object-cover border border-rose-200 shadow-sm" onerror="this.style.display='none'">
                                <span class="text-slate-300 font-light text-base">×</span>
                                <img src="/images/IMG_0048.PNG" class="h-6 object-contain" onerror="this.style.display='none'">
                            </div>
                            <h2 class="font-extrabold text-base text-slate-900 tracking-tight leading-none pt-1">Markasnack x Glytch</h2>
                            <p class="text-[9px] font-bold uppercase tracking-wider text-rose-500">Sales Receipt & Invoice</p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-black text-xl text-slate-900 tracking-wider">INVOICE</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Sales Invoice</p>
                        </div>
                    </div>

                    <!-- Info Box Transaksi (Gaya TinyBites Card) -->
                    <div class="bg-rose-50/60 border border-rose-100/80 rounded-2xl p-4 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Invoice:</span>
                            <span class="font-bold text-slate-900 font-mono text-[11px]" x-text="lastInvoice ? lastInvoice.invoice_number : '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Date:</span>
                            <span class="font-bold text-slate-800 font-mono text-[11px]" x-text="lastInvoice ? lastInvoice.date : '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Payment:</span>
                            <span class="font-bold text-indigo-600 uppercase tracking-wide text-[11px]" x-text="lastInvoice ? lastInvoice.payment_method : '-'"></span>
                        </div>
                    </div>

                    <!-- Rincian Barang Terjual -->
                    <div class="space-y-3 divide-y divide-slate-100/80">
                        <template x-for="(line, idx) in (lastInvoice ? lastInvoice.lines : [])" :key="idx">
                            <div class="pt-2.5 first:pt-0 space-y-1">
                                <div class="flex justify-between items-start gap-2">
                                    <h5 class="font-bold text-slate-900 text-xs leading-snug" x-text="line.name"></h5>
                                    <span class="font-bold text-slate-900 text-xs font-mono whitespace-nowrap" x-text="'Rp ' + formatNumber(line.line_total)"></span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-slate-400">
                                    <span>
                                        Qty: <strong class="text-slate-700" x-text="line.qty"></strong>
                                        &nbsp;•&nbsp; 
                                        Harga: <strong class="text-slate-700" x-text="'Rp ' + formatNumber(line.unit_price)"></strong>
                                        <template x-if="line.portion && line.portion !== 'pcs'">
                                            <span x-text="' (' + line.portion + ')'"></span>
                                        </template>
                                    </span>
                                    <span class="text-[9px] uppercase font-semibold text-slate-400" x-text="line.store"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Ringkasan Totals & Subtotal -->
                    <div class="border-t border-slate-200 pt-3 space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-semibold font-mono" x-text="'Rp ' + formatNumber(lastInvoice ? lastInvoice.subtotal_amount : 0)"></span>
                        </div>
                        <template x-if="lastInvoice && lastInvoice.discount_amount > 0">
                            <div class="flex justify-between text-emerald-600">
                                <span x-text="'Discount' + (lastInvoice.discount_name ? ' (' + lastInvoice.discount_name + ')' : '')"></span>
                                <span class="font-semibold font-mono" x-text="'- Rp ' + formatNumber(lastInvoice.discount_amount)"></span>
                            </div>
                        </template>
                        <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-dashed border-slate-200">
                            <span>TOTAL</span>
                            <span class="text-indigo-600 font-mono" x-text="'Rp ' + formatNumber(lastInvoice ? lastInvoice.total_amount : 0)"></span>
                        </div>
                        <template x-if="lastInvoice && lastInvoice.payment_method === 'cash'">
                            <div class="space-y-1 pt-1 text-[11px] text-slate-500">
                                <div class="flex justify-between">
                                    <span>Bayar (Cash)</span>
                                    <span class="font-mono" x-text="'Rp ' + formatNumber(lastInvoice.pay_amount)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kembali</span>
                                    <span class="font-mono font-bold text-emerald-600" x-text="'Rp ' + formatNumber(lastInvoice.change_amount)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Footer Message -->
                    <div class="text-center text-[10px] text-slate-400 pt-2 border-t border-slate-100 space-y-1">
                        <p class="font-medium">Thank you for your purchase! 💕</p>
                        <div class="pt-1 text-[10px] text-slate-600">
                            <p class="text-slate-400 text-[9px]">Our Instagram:</p>
                            <p class="font-bold text-slate-800 tracking-wider">@markasnack_</p>
                            <p class="font-bold text-slate-800 tracking-wider">@glytch.co</p>
                        </div>
                        <p class="text-[8px] text-slate-300 pt-1">Markasnack x Glytch Official POS</p>
                    </div>
                                    
                <!-- Action Buttons Struk -->
                <div class="flex flex-col gap-2 pt-2 print:hidden border-t border-slate-100">
                    <button type="button" @click="printReceiptDirect()" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition cursor-pointer flex items-center justify-center gap-2">
                        <span>⚡</span> Cetak Direct Bluetooth (RPP02N 58mm)
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="closeReceipt()" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">Selesai</button>
                        <button type="button" @click="printReceipt()" class="w-full py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-xl text-xs transition cursor-pointer">Print Dialog / PDF</button>
                    </div>
                    <template x-if="lastInvoice && lastInvoice.invoice_number">
                        <button type="button" @click="openVoidModalWithInvoice(lastInvoice.invoice_number)" class="w-full py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-xl text-xs border border-rose-200 transition cursor-pointer">
                            🚫 Batalkan Transaksi Ini (Void)
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- MODAL VOID / BATALKAN TRANSAKSI -->
    <template x-teleport="body">
        <div x-show="openVoidModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            <div @click.away="openVoidModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Batalkan Transaksi (Void)</h3>
                        <p class="text-xs text-slate-400">Pengembalian stok FIFO & pembatalan omzet</p>
                    </div>
                    <button type="button" @click="openVoidModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form @submit.prevent="submitVoid()" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ID Transaksi / Nomor Nota</label>
                        <input type="text" x-model="voidTransactionId" required placeholder="Ketik ID atau No. Struk (Contoh: INV-202611... / 12)" class="w-full px-3 py-2 border rounded-xl text-xs font-bold bg-white">
                        <p class="text-[10px] text-slate-400 mt-1">Bisa diisi ID angka murni (12) atau Nomor Nota lengkap (INV-...).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan</label>
                        <select x-model="voidReason" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                            <option value="">-- Pilih Alasan --</option>
                            <option value="Salah Input Barang / Jumlah">Salah Input Barang / Jumlah</option>
                            <option value="Pembeli Batal / Uang Kurang">Pembeli Batal / Uang Kurang</option>
                            <option value="Tukar Barang">Tukar Barang</option>
                            <option value="Kesalahan Sistem / Dobel Print">Kesalahan Sistem / Dobel Print</option>
                        </select>
                    </div>

                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-800 space-y-1">
                        <p class="font-bold">⚠️ Perhatian:</p>
                        <p>Membatalkan transaksi akan mengembalikan stok fisik secara otomatis ke batch FIFO asal dan mengeluarkan transaksi dari laporan keuangan.</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="openVoidModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                        <button type="submit" :disabled="voiding" class="px-4 py-2 bg-rose-600 disabled:bg-slate-300 text-white font-bold rounded-xl text-xs shadow-md cursor-pointer" x-text="voiding ? 'Memproses...' : 'Konfirmasi Void Transaksi'"></button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

<script>
function posSystem() {
    return {
        searchQuery: '',
        selectedStore: '',
        selectedCategory: '',
        products: @json($products),
        discounts: @json($discounts),
        cart: [],
        selectedDiscountId: '',
        openPayModal: false,
        openReceiptModal: false,
        openPortionModal: false,
        openVoidModal: false,
        selectedPortionProduct: null,
        lastInvoice: null,
        paymentMethod: 'cash',
        payAmount: 0,
        processing: false,
        voidTransactionId: '',
        voidReason: '',
        voiding: false,

        // State Bluetooth Web API
        btDevice: null,
        btCharacteristic: null,
        btConnected: false,

        // Toast Notification Custom
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        showNotification(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        // FUNGSI WEB BLUETOOTH DIRECT ESC/POS UNTUK RPP02N (58mm)
        async connectBluetooth() {
            if (!navigator.bluetooth) {
                this.showNotification('Browser ini tidak mendukung Web Bluetooth API. Gunakan Chrome atau Edge.', 'error');
                return;
            }

            try {
                this.showNotification('Mencari printer Bluetooth...', 'info');
                const device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: [
                        '0000ffe0-0000-1000-8000-00805f9b34fb', // Standard SPP Bluetooth Thermal
                        '000018f0-0000-1000-8000-00805f9b34fb',
                        '49535343-fe7d-4ae5-8fa9-9fafd205e455'
                    ]
                });

                this.showNotification('Menghubungkan ke ' + (device.name || 'Printer') + '...', 'info');
                const server = await device.gatt.connect();

                let service;
                const knownServices = [
                    '0000ffe0-0000-1000-8000-00805f9b34fb',
                    '000018f0-0000-1000-8000-00805f9b34fb',
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455'
                ];

                for (const sUuid of knownServices) {
                    try {
                        service = await server.getPrimaryService(sUuid);
                        if (service) break;
                    } catch (e) {}
                }

                if (!service) {
                    const services = await server.getPrimaryServices();
                    if (services.length > 0) service = services[0];
                }

                if (!service) throw new Error('Layanan printer tidak ditemukan.');

                const characteristics = await service.getCharacteristics();
                if (characteristics.length === 0) throw new Error('Karakteristik printer tidak ditemukan.');

                this.btDevice = device;
                this.btCharacteristic = characteristics[0];
                this.btConnected = true;

                this.showNotification('Printer RPP02N berhasil terhubung!', 'success');

                device.addEventListener('gattserverdisconnected', () => {
                    this.btConnected = false;
                    this.btCharacteristic = null;
                    this.showNotification('Koneksi printer terputus.', 'warning');
                });
            } catch (err) {
                this.showNotification('Gagal Konek: ' + err.message, 'error');
            }
        },

        formatLine32(left, right) {
            left = String(left);
            right = String(right);
            const maxLen = 32;
            const spaceNeeded = maxLen - left.length - right.length;
            if (spaceNeeded > 0) {
                return left + ' '.repeat(spaceNeeded) + right + '\n';
            }
            return left.substring(0, maxLen - right.length - 1) + ' ' + right + '\n';
        },

        async printReceiptDirect() {
            if (!this.btConnected || !this.btCharacteristic) {
                this.showNotification('Mencoba menyambungkan ke RPP02N...', 'info');
                await this.connectBluetooth();
                if (!this.btConnected) return;
            }

            try {
                this.showNotification('Mengirim data ke RPP02N...', 'info');
                const encoder = new TextEncoder();
                let bytes = [];

                const addBytes = (arr) => bytes.push(...arr);
                const addStr = (str) => bytes.push(...encoder.encode(str));

                // 1. Reset / Init ESC/POS
                addBytes([0x1B, 0x40]);

                // 2. Header (Align Center)
                addBytes([0x1B, 0x61, 0x01]);
                addBytes([0x1B, 0x45, 0x00]); // Bold OFF (Teks Biasa)
                addStr("MARKASNACK x GLYTCH.co\n");
                addStr("Sales Receipt & Invoice\n");
                addStr((this.lastInvoice ? this.lastInvoice.invoice_number : '') + "\n");
                addStr((this.lastInvoice ? this.lastInvoice.date : '') + "\n");
                addStr("--------------------------------\n");

                // 3. Items (Align Left)
                addBytes([0x1B, 0x61, 0x00]);
                if (this.lastInvoice && this.lastInvoice.lines) {
                    for (const line of this.lastInvoice.lines) {
                        addStr(line.name.substring(0, 32) + "\n");
                        const detailStr = ` ${line.qty}x ${line.portion}`;
                        const priceStr = "Rp " + this.formatNumber(line.line_total);
                        addStr(this.formatLine32(detailStr, priceStr));
                    }
                }
                addStr("--------------------------------\n");

                // 4. Summary & Diskon
                if (this.lastInvoice && this.lastInvoice.discount_amount > 0) {
                    addStr(this.formatLine32("Subtotal", "Rp " + this.formatNumber(this.lastInvoice.subtotal_amount)));
                    addStr(this.formatLine32("Diskon", "-Rp " + this.formatNumber(this.lastInvoice.discount_amount)));
                }

                addBytes([0x1B, 0x45, 0x00]); // Bold OFF (Teks Biasa)
                addStr(this.formatLine32("TOTAL", "Rp " + this.formatNumber(this.lastInvoice ? this.lastInvoice.total_amount : 0)));

                addStr(this.formatLine32("Bayar", "Rp " + this.formatNumber(this.lastInvoice ? this.lastInvoice.pay_amount : 0)));
                addStr(this.formatLine32("Kembali", "Rp " + this.formatNumber(this.lastInvoice ? this.lastInvoice.change_amount : 0)));

                // 5. Footer
                addStr("--------------------------------\n");
                addBytes([0x1B, 0x61, 0x01]); // Align Center
                addStr("Thank you for your purchase!\n\n");
                addStr("Our Instagram:\n");

                // Atur jarak antar karakter agak renggang (+2 dot)
                addBytes([0x1B, 0x20, 0x02]); 
                addStr("@markasnack_\n");
                addStr("@glytch.co\n");

                // Kembalikan jarak karakter ke normal (0 dot)
                addBytes([0x1B, 0x20, 0x00]); 
                addStr("\n\n\n\n"); // Feed Kertas

                // Kirim byte data per chunk ke printer
                const chunkSize = 100;
                const uint8Array = new Uint8Array(bytes);
                for (let i = 0; i < uint8Array.length; i += chunkSize) {
                    const chunk = uint8Array.slice(i, i + chunkSize);
                    if (this.btCharacteristic.writeValueWithoutResponse) {
                        await this.btCharacteristic.writeValueWithoutResponse(chunk);
                    } else {
                        await this.btCharacteristic.writeValue(chunk);
                    }
                }

                this.showNotification('Struk berhasil dicetak ke RPP02N!', 'success');
            } catch (err) {
                this.showNotification('Gagal Cetak Direct: ' + err.message, 'error');
            }
        },

        get filteredProducts() {
            return this.products.filter(p => {
                const matchSearch = p.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchStore = !this.selectedStore || p.store_id == this.selectedStore;
                const matchCategory = !this.selectedCategory || p.category_id == this.selectedCategory;
                return matchSearch && matchStore && matchCategory;
            });
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
        },

        get selectedDiscount() {
            return this.discounts.find(d => String(d.id) === String(this.selectedDiscountId)) || null;
        },

        get discountAmount() {
            const d = this.selectedDiscount;
            if (!d) return 0;
            const eligible = this.cart
                .filter(i => this.isEligible(d, i))
                .reduce((sum, i) => sum + i.price * i.qty, 0);
            if (eligible <= 0) return 0;
            const raw = d.type === 'percent' ? eligible * Number(d.value) / 100 : Number(d.value);
            return Math.min(eligible, Math.round(raw));
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },

        isEligible(d, item) {
            if (d.scope === 'store') return item.store_id == d.store_id;
            if (d.scope === 'product') return item.product_id == d.product_id;
            return true;
        },

        getTotalStock(product) {
            return Number(product.total_stock) || 0;
        },

        unitLabel(product) {
            return product.unit_type === 'gram' ? 'gr' : product.unit_type;
        },

        portionLabel(item) {
            if (item.unit_type === 'pcs') return item.qty + ' pcs';
            return item.qty + ' x ' + this.formatNumber(item.amount) + ' ' + (item.unit_type === 'gram' ? 'gr' : item.unit_type);
        },

        priceLabel(product) {
            if (product.unit_type === 'pcs') {
                const price = Number(product.sell_price) || 0;
                return price > 0 ? 'Rp ' + this.formatNumber(price) : 'Harga belum diatur';
            }
            const prices = (product.price_templates || []).map(t => Number(t.price));
            return prices.length ? 'Mulai Rp ' + this.formatNumber(Math.min(...prices)) : 'Template belum diatur';
        },

        unitsInCart(productId) {
            return this.cart
                .filter(i => i.product_id === productId)
                .reduce((sum, i) => sum + i.qty * (i.unit_type === 'pcs' ? 1 : Number(i.amount)), 0);
        },

        canAdd(product, amount) {
            if (!product) return false;
            const units = product.unit_type === 'pcs' ? 1 : Number(amount);
            return this.unitsInCart(product.id) + units <= this.getTotalStock(product);
        },

        selectProduct(product) {
            if (this.getTotalStock(product) <= 0) {
                this.showNotification('Stok ' + product.name + ' habis.', 'warning');
                return;
            }

            if (product.unit_type === 'pcs') {
                const price = Number(product.sell_price) || 0;
                if (price <= 0) {
                    this.showNotification('Harga jual ' + product.name + ' belum diatur.', 'warning');
                    return;
                }
                this.addToCart(product, null, price);
                return;
            }

            if (!product.price_templates || product.price_templates.length === 0) {
                this.showNotification('Template harga ' + product.name + ' belum diatur.', 'warning');
                return;
            }
            this.selectedPortionProduct = product;
            this.openPortionModal = true;
        },

        addToCart(product, amount, price) {
            if (!this.canAdd(product, amount)) {
                this.showNotification('Stok ' + product.name + ' tidak cukup.', 'warning');
                return;
            }

            const key = product.id + '|' + (amount === null ? 'pcs' : amount);
            const existing = this.cart.find(i => i.key === key);
            if (existing) {
                existing.qty++;
                return;
            }

            this.cart.push({
                key: key,
                product_id: product.id,
                store_id: product.store_id,
                store_name: product.store ? product.store.name : '-',
                name: product.name,
                unit_type: product.unit_type,
                amount: amount === null ? null : Number(amount),
                price: Number(price),
                qty: 1
            });
        },

        changeQty(item, delta) {
            if (delta > 0) {
                const product = this.products.find(p => p.id === item.product_id);
                if (!this.canAdd(product, item.amount)) {
                    this.showNotification('Stok ' + item.name + ' tidak cukup.', 'warning');
                    return;
                }
            }
            item.qty += delta;
            if (item.qty <= 0) this.removeItem(item);
        },

        removeItem(item) {
            this.cart = this.cart.filter(i => i.key !== item.key);
        },

        formatNumber(num) {
            const cleanNum = Number(num) || 0;
            return new Intl.NumberFormat('id-ID').format(cleanNum);
        },

        submitCheckout() {
            if (this.processing) return;

            if (this.paymentMethod === 'cash' && Number(this.payAmount) < this.grandTotal) {
                this.showNotification('Uang pembayaran kurang!', 'warning');
                return;
            }

            this.processing = true;

            fetch("{{ route('pos.checkout') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    cart: this.cart.map(i => ({ product_id: i.product_id, template_amount: i.amount, qty: i.qty })),
                    discount_id: this.selectedDiscountId || null,
                    pay_amount: this.paymentMethod === 'cash' ? (Number(this.payAmount) || 0) : this.grandTotal,
                    payment_method: this.paymentMethod
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.lastInvoice = data.invoice;
                    this.openPayModal = false;
                    this.openReceiptModal = true;
                    this.cart = [];
                    this.payAmount = 0;
                    this.selectedDiscountId = '';
                    this.showNotification('Transaksi Berhasil!', 'success');
                } else {
                    this.showNotification('Gagal: ' + (data.error || 'Terjadi kesalahan.'), 'error');
                }
            })
            .catch(err => this.showNotification('Error: ' + err.message, 'error'))
            .finally(() => { this.processing = false; });
        },

        openVoidModalWithInvoice(invoiceNo) {
            this.voidTransactionId = invoiceNo;
            this.openReceiptModal = false;
            this.openVoidModal = true;
        },

        submitVoid() {
            if (!this.voidTransactionId) {
                this.showNotification('Masukkan ID atau No. Nota yang ingin dibatalkan.', 'warning');
                return;
            }
            if (!this.voidReason) {
                this.showNotification('Pilih alasan pembatalan.', 'warning');
                return;
            }

            this.voiding = true;

            fetch("/transactions/" + encodeURIComponent(this.voidTransactionId) + "/void", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    reason: this.voidReason
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message || 'Transaksi berhasil dibatalkan!', 'success');
                    this.openVoidModal = false;
                    this.voidTransactionId = '';
                    this.voidReason = '';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    this.showNotification('Gagal Void: ' + (data.error || 'Terjadi kesalahan.'), 'error');
                }
            })
            .catch(err => this.showNotification('Error: ' + err.message, 'error'))
            .finally(() => { this.voiding = false; });
        },

        closeReceipt() {
            this.openReceiptModal = false;
            window.location.reload();
        },

        printReceipt() {
            window.print();
        }
    }
}
</script>
@endsection