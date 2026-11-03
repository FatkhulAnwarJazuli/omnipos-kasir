@extends('layouts.app')

@section('content')
<div x-data="discountPage()" class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Diskon & Promo</h2>
            <p class="text-xs text-slate-500">Buat promo, lalu hidupkan atau matikan kapan saja. Diskon yang aktif bisa dipilih kasir di layar POS.</p>
        </div>
        <button @click="openForm()" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-indigo-600 px-5 py-3 text-center text-xs font-bold text-white hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Diskon
        </button>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 text-xs font-medium">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 text-xs font-medium">
            <p class="font-bold mb-1">Diskon belum tersimpan, mohon periksa:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabel Diskon -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                        <th class="py-3.5 px-6">Nama Promo</th>
                        <th class="py-3.5 px-6">Potongan</th>
                        <th class="py-3.5 px-6">Berlaku Untuk</th>
                        <th class="py-3.5 px-6">Periode</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($discounts as $d)
                        @php
                            $today = now()->startOfDay();
                            if (!$d->is_active) {
                                $status = ['Nonaktif', 'bg-slate-100 text-slate-500 border-slate-200'];
                            } elseif ($d->start_date && $d->start_date->gt($today)) {
                                $status = ['Terjadwal', 'bg-amber-50 text-amber-700 border-amber-200'];
                            } elseif ($d->end_date && $d->end_date->lt($today)) {
                                $status = ['Berakhir', 'bg-rose-50 text-rose-700 border-rose-200'];
                            } else {
                                $status = ['Aktif', 'bg-emerald-50 text-emerald-700 border-emerald-200'];
                            }

                            if ($d->start_date && $d->end_date) {
                                $period = $d->start_date->format('d/m/Y') . ' – ' . $d->end_date->format('d/m/Y');
                            } elseif ($d->start_date) {
                                $period = 'Mulai ' . $d->start_date->format('d/m/Y');
                            } elseif ($d->end_date) {
                                $period = 'Sampai ' . $d->end_date->format('d/m/Y');
                            } else {
                                $period = 'Selalu berlaku';
                            }

                            $target = match ($d->scope) {
                                'store'   => 'Toko: ' . ($d->store->name ?? '-'),
                                'product' => 'Produk: ' . ($d->product->name ?? '-'),
                                default   => 'Semua produk',
                            };

                            $editData = [
                                'id'         => $d->id,
                                'name'       => $d->name,
                                'type'       => $d->type,
                                'value'      => (float) $d->value,
                                'scope'      => $d->scope,
                                'store_id'   => $d->store_id,
                                'product_id' => $d->product_id,
                                'start_date' => $d->start_date ? $d->start_date->format('Y-m-d') : null,
                                'end_date'   => $d->end_date ? $d->end_date->format('Y-m-d') : null,
                                'is_active'  => (bool) $d->is_active,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-6 font-bold text-slate-900 text-sm whitespace-nowrap">{{ $d->name }}</td>
                            <td class="py-3.5 px-6 font-extrabold text-indigo-600 whitespace-nowrap">
                                {{ $d->type === 'percent' ? rtrim(rtrim(number_format($d->value, 2, ',', '.'), '0'), ',') . '%' : 'Rp ' . number_format($d->value, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 whitespace-nowrap">{{ $target }}</td>
                            <td class="py-3.5 px-6 text-slate-500 whitespace-nowrap">{{ $period }}</td>
                            <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold border {{ $status[1] }}">{{ $status[0] }}</span>
                            </td>
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form action="{{ route('discounts.toggle', $d->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 {{ $d->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-200/60' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200/60' }} font-bold rounded-lg text-[11px] transition border cursor-pointer">
                                            {{ $d->is_active ? 'Matikan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <button type="button" @click="openForm(@js($editData))" class="px-2 py-1.5 text-slate-600 hover:bg-slate-100 rounded-lg font-bold text-[11px] cursor-pointer">Edit</button>
                                    <form action="{{ route('discounts.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Yakin hapus diskon ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2 py-1.5 text-rose-600 hover:bg-rose-50 rounded-lg font-bold text-[11px] transition cursor-pointer">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">Belum ada diskon. Klik "Tambah Diskon" kalau ingin membuat promo. Kalau tidak dipakai, tidak apa-apa, kasir berjalan seperti biasa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL FORM DISKON (tambah & edit) -->
    <template x-teleport="body">
        <div x-show="openModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openModal = false" class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 space-y-4 my-auto">
                <h3 class="font-bold text-slate-900 text-sm" x-text="isEdit ? 'Edit Diskon' : 'Tambah Diskon Baru'"></h3>

                <form :action="formAction" method="POST" class="space-y-3">
                    @csrf
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Promo</label>
                        <input type="text" name="name" x-model="form.name" required placeholder="Contoh: Promo Akhir Bulan" class="w-full px-3 py-2 border rounded-xl text-xs">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Potongan</label>
                            <select name="type" x-model="form.type" class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                <option value="percent">Persen (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" x-text="form.type === 'percent' ? 'Besar Diskon (%)' : 'Potongan (Rp)'"></label>
                            <input type="number" name="value" x-model="form.value" required min="0.01" step="any" :max="form.type === 'percent' ? 100 : null" :placeholder="form.type === 'percent' ? 'Contoh: 10' : 'Contoh: 5000'" class="w-full px-3 py-2 border rounded-xl text-xs font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Berlaku Untuk</label>
                        <select name="scope" x-model="form.scope" class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                            <option value="all">Semua produk</option>
                            <option value="store">Satu toko</option>
                            <option value="product">Produk tertentu</option>
                        </select>
                    </div>

                    <template x-if="form.scope === 'store'">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Toko</label>
                            <select name="store_id" x-model="form.store_id" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                <option value="">-- Pilih toko --</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <template x-if="form.scope === 'product'">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Produk</label>
                            <select name="product_id" x-model="form.product_id" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                <option value="">-- Pilih produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->store->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai (opsional)</label>
                            <input type="date" name="start_date" x-model="form.start_date" class="w-full px-3 py-2 border rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Selesai (opsional)</label>
                            <input type="date" name="end_date" x-model="form.end_date" class="w-full px-3 py-2 border rounded-xl text-xs">
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400">Kosongkan tanggal kalau promo berlaku terus selama statusnya aktif.</p>

                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-slate-300">
                        Aktif (bisa dipilih kasir)
                    </label>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md cursor-pointer">Simpan Diskon</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function discountPage() {
    const blank = () => ({
        name: '', type: 'percent', value: '', scope: 'all',
        store_id: '', product_id: '', start_date: '', end_date: '', is_active: true
    });

    return {
        openModal: false,
        isEdit: false,
        formAction: "{{ route('discounts.store') }}",
        form: blank(),

        openForm(d = null) {
            if (d) {
                this.isEdit = true;
                this.formAction = "{{ url('discounts') }}/" + d.id;
                this.form = {
                    name: d.name, type: d.type, value: d.value, scope: d.scope,
                    store_id: d.store_id ?? '', product_id: d.product_id ?? '',
                    start_date: d.start_date ?? '', end_date: d.end_date ?? '', is_active: !!d.is_active
                };
            } else {
                this.isEdit = false;
                this.formAction = "{{ route('discounts.store') }}";
                this.form = blank();
            }
            this.openModal = true;
        }
    };
}
</script>
@endsection