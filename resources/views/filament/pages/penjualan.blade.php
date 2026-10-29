<x-filament-panels::page>
    <style>
    .pos-layout {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
        gap: 1.5rem;
        align-items: start;
    }

    .pos-left,
    .pos-right {
        min-width: 0;
    }

    .pos-left {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .pos-right {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        position: sticky;
        top: 1rem;
    }

    .pos-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.06),
            0 1px 2px rgba(0, 0, 0, 0.04);
        border: 1px solid rgb(229 231 235);
    }

    .pos-product-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        max-height: 22rem;
        overflow-y: auto;
    }

    .pos-product-image {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        background: #f3f4f6;
    }

    .pos-cart-item {
        display: flex;
        gap: .75rem;
        padding: .75rem;
        border: 1px solid rgb(229 231 235);
        border-radius: .75rem;
        background: #f9fafb;
    }

    .pos-cart-image {
        width: 56px;
        height: 56px;
        flex: 0 0 56px;
        border-radius: .5rem;
        object-fit: cover;
        background: #f3f4f6;
    }

    /* ===================== SEARCH BAR (diperbaiki) ===================== */
    .pos-search-wrap {
        display: flex;
        align-items: stretch;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .pos-search-field {
        position: relative;
        flex: 1 1 220px;
        min-width: 0;
    }

    .pos-search-icon {
        position: absolute;
        left: .875rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1rem;
        height: 1rem;
        color: rgb(156 163 175);
        pointer-events: none;
    }

    .pos-search-input {
        width: 100%;
        height: 2.75rem;
        border-radius: 0.75rem;
        border: 1px solid rgb(226 232 240);
        background: rgb(249 250 251);
        padding: 0 2.5rem 0 2.5rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: rgb(17 24 39);
        text-align: left;
        transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .pos-search-input::placeholder {
        color: rgb(156 163 175);
    }

    .pos-search-input:hover {
        border-color: rgb(203 213 225);
    }

    .pos-search-input:focus {
        outline: none;
        background: white;
        border-color: rgb(var(--primary-500, 99 102 241));
        box-shadow: 0 0 0 3px rgb(var(--primary-500, 99 102 241) / 0.15);
    }

    .pos-search-clear {
        position: absolute;
        right: .625rem;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 9999px;
        color: rgb(156 163 175);
        transition: background-color .15s ease, color .15s ease;
    }

    .pos-search-clear:hover {
        background: rgb(243 244 246);
        color: rgb(239 68 68);
    }

    .pos-cart-badge {
        display: inline-flex;
        align-items: center;
        gap: .375rem;
        white-space: nowrap;
        border-radius: 9999px;
        padding: 0 .875rem;
        height: 2.75rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .dark .pos-search-input {
        background: rgb(31 41 55 / .6);
        border-color: rgb(55 65 81);
        color: white;
    }

    .dark .pos-search-input:focus {
        background: rgb(31 41 55);
    }

    .dark .pos-search-clear:hover {
        background: rgb(55 65 81);
    }
    /* ===================================================================== */

    @media (max-width: 1024px) {
        .pos-layout {
            grid-template-columns: 1fr;
        }

        .pos-right {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .pos-product-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .pos-product-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pos-card {
            padding: 1rem;
        }

        .pos-search-wrap {
            flex-wrap: wrap;
        }

        .pos-cart-badge {
            flex: 1 1 auto;
            justify-content: center;
        }
    }

    .dark .pos-card {
        background: rgb(17 24 39);
        border-color: rgb(55 65 81);
    }

    .dark .pos-cart-item {
        background: rgb(31 41 55 / .5);
        border-color: rgb(55 65 81);
    }

    .dark .pos-product-image,
    .dark .pos-cart-image {
        background: rgb(31 41 55);
    }
</style>
    <div class="pos-layout">

    {{-- ==================== KIRI: ITEM BARANG ==================== --}}
    <div class="pos-left">

        {{-- Pencarian barang --}}
        <div class="pos-card p-4">
            <div class="pos-search-wrap">
                <div class="pos-search-field">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         class="pos-search-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama atau kode barang..."
                        autocomplete="off"
                        class="pos-search-input"
                    />
                    @if ($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="pos-search-clear"
                            aria-label="Bersihkan pencarian"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                            </svg>
                        </button>
                    @endif
                </div>
                <span class="pos-cart-badge bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-400 ring-1 ring-inset ring-primary-600/20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                        <path d="M1 1.75A.75.75 0 011.75 1h1.628a1.75 1.75 0 011.734 1.51L5.18 3a2.25 2.25 0 00-2.25 2.25v9.75A2.25 2.25 0 005.18 17h9.64a2.25 2.25 0 002.25-2.25V5.25A2.25 2.25 0 0014.82 3l.068-.49A1.75 1.75 0 0116.622 1h1.628a.75.75 0 010 1.5h-1.628a.25.25 0 00-.248.216L15.98 4.5H4.02l-.394-2.784A.25.25 0 003.378 1.5H1.75A.75.75 0 011 1.75z"/>
                    </svg>
                    {{ count($this->cart) }} item
                </span>
            </div>
        </div>

        {{-- Grid pilihan barang --}}
        <div class="pos-card p-12">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Pilih Barang</h3>
                <span class="text-xs text-gray-400">{{ $this->barangs->count() }} tersedia</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[22rem] overflow-y-auto pr-1 scrollbar-thin">
                @forelse ($this->barangs as $barang)
                    @php
                        $imageUrl = $barang->gambarUtama?->path
                            ? \Illuminate\Support\Facades\Storage::url($barang->gambarUtama->path)
                            : asset('images/placeholder.png');
                    @endphp

                    <button
                        type="button"
                        wire:click="addToCart({{ $barang->id }})"
                        @disabled($barang->stok < 1)
                        class="group relative text-left rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/80 hover:border-primary-500 hover:bg-white dark:hover:bg-gray-800 hover:shadow-md transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed overflow-hidden"
                    >
                        <div class="aspect-square w-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center overflow-hidden">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $barang->nama_barang }}"
                                class="pos-product-image"
                                onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';"
                            />
                        </div>

                        <div class="p-6 space-y-0.5">
                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate leading-tight">
                                {{ $barang->nama_barang }}
                            </p>
                            <p class="text-[11px] font-medium text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] {{ $barang->stok < 1 ? 'text-danger-500 font-medium' : 'text-gray-400' }}">
                                Stok: {{ $barang->stok }}
                            </p>
                        </div>

                        @if($barang->stok < 1)
                            <div class="absolute inset-0 bg-white/60 dark:bg-black/40 flex items-center justify-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-danger-600">Habis</span>
                            </div>
                        @endif
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-12 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <p class="text-sm">Barang tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ==================== KANAN: KERANJANG + INFORMASI + NILAI TRANSAKSI ==================== --}}
    <div class="pos-right">

        {{-- Keranjang --}}
        <div class="pos-card p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-4">Keranjang</h3>

            <div class="space-y-3 max-h-[15rem] overflow-y-auto pr-1 scrollbar-thin">
                @forelse ($this->cart as $barangId => $item)
                    @php
                        $cartImage = !empty($item['gambar'])
                            ? $item['gambar']
                            : asset('images/placeholder.png');
                    @endphp

                    <div wire:key="cart-{{ $barangId }}"
                         class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40 p-3 transition hover:border-gray-300 dark:hover:border-gray-600">

                        <div class="flex gap-3">
                            {{-- Gambar --}}
                            <div class="shrink-0">
                                <div class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-800 overflow-hidden flex items-center justify-center ring-1 ring-gray-200 dark:ring-gray-700">
                                    <img
                                        src="{{ $cartImage }}"
                                        class="pos-cart-image"
                                        alt="{{ $item['nama'] }}"
                                        onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';"
                                    />
                                </div>
                            </div>

                            {{-- Info + kontrol --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $item['nama'] }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $item['kode'] }} · Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                    </div>
                                    <button type="button" wire:click="removeFromCart({{ $barangId }})"
                                            class="shrink-0 p-1 rounded-md text-gray-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="mt-2.5 flex items-center justify-between">
                                    {{-- Stepper --}}
                                    <div class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 overflow-hidden">
                                        <button type="button" wire:click="decrementQty({{ $barangId }})"
                                                class="h-8 w-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            −
                                        </button>
                                        <input type="number" min="1" max="{{ $item['stok'] }}"
                                               wire:change="updateQty({{ $barangId }}, $event.target.value)"
                                               value="{{ $item['jumlah'] }}"
                                               class="w-12 text-center border-0 bg-transparent text-sm font-medium text-gray-900 dark:text-white focus:ring-0 py-1.5" />
                                        <button type="button" wire:click="incrementQty({{ $barangId }})"
                                                class="h-8 w-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            +
                                        </button>
                                    </div>

                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-14 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <p class="text-sm font-medium">Keranjang masih kosong</p>
                        <p class="text-xs mt-1">Klik barang di kiri untuk menambahkan</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Informasi Transaksi --}}
        <div class="pos-card p-5 space-y-5">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-primary-500">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                </svg>
                Informasi Transaksi
            </h3>

            {{-- Kode transaksi --}}
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Kode Transaksi</p>
                <div class="rounded-xl bg-gray-100 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-gray-500 italic flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    Otomatis saat disimpan
                </div>
            </div>

            {{-- Form Filament (Pelanggan sekarang punya tombol "+" untuk buka modal tambah baru) --}}
            <form wire:submit.prevent="simpan" class="space-y-4">
                {{ $this->form }}
            </form>

            {{-- Status --}}
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</p>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 dark:bg-success-500/10 px-3 py-1.5 text-xs font-semibold text-success-700 dark:text-success-400 ring-1 ring-inset ring-success-600/20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                    </svg>
                    Akan tersimpan sebagai "Selesai"
                </span>
            </div>
        </div>

        {{-- Nilai Transaksi --}}
        <div class="pos-card p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-primary-500">
                    <path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.32.383.32.564 0 .247-.134.577-.585.787A3.796 3.796 0 016.75 10.25v-.217A4.57 4.57 0 018.33 8.62z" />
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-6a.75.75 0 01.75.75v.316a3.78 3.78 0 011.784.573c.622.422 1.016 1.023 1.016 1.711 0 .10.0.0.2.66a.75.75 0 01-1.248.832A2.292 2.292 0 0012.5 8.75c0-.275-.132-.523-.417-.716a2.292 2.292 0 00-1.333-.409h-.25a.75.75 0 01-.75-.75V4.75A.75.75 0 0110 4zm-3.197 6.036A4.57 4.57 0 018.33 8.62a.75.75 0 00-1.086-1.036 6.07 6.07 0 00-1.55 1.724.75.75 0 001.11.995zM10 15.25a.75.75 0 01-.75-.75v-.316a3.78 3.78 0 01-1.784-.573C6.844 13.19 6.45 12.588 6.45 11.9c0-.34.134-.66.366-.875a.75.75 0 011.05 1.05c-.192.168.1.1.184.35 0 .275.132.523.417.716a2.292 2.292 0 001.333.409h.25a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75z" clip-rule="evenodd" />
                </svg>
                Nilai Transaksi
            </h3>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                    <span>Subtotal</span>
                    <span class="font-medium">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                    <span>Total Diskon</span>
                    <span class="font-medium text-danger-500">− Rp {{ number_format($this->totalDiskon, 0, ',', '.') }}</span>
                </div>

                <div class="border-t border-dashed border-gray-200 dark:border-gray-700 pt-3 flex justify-between text-base font-bold text-gray-900 dark:text-white">
                    <span>Total Bayar</span>
                    <span>Rp {{ number_format($this->totalBayar, 0, ',', '.') }}</span>
                </div>

                {{-- INPUT UANG DITERIMA (hanya muncul kalau Tunai) --}}
                     <div class="flex justify-between items-center pt-1">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Kembalian</span>
                        <span class="text-lg font-bold {{ $this->kembalian > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}">
                            Rp {{ number_format($this->kembalian, 0, ',', '.') }}
                        </span>
                    </div>
                 </div>

            {{-- Tombol Simpan --}}
            <button
                type="button"
                wire:click="simpan"
                wire:loading.attr="disabled"
                wire:target="simpan"
                class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:opacity-60 disabled:cursor-not-allowed transition-all"
            >
                <svg wire:loading.remove wire:target="simpan" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                </svg>
                <svg wire:loading wire:target="simpan" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span wire:loading.remove wire:target="simpan">Simpan Transaksi</span>
                <span wire:loading wire:target="simpan">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>

      @if($showNotaModal && $lastTransaksiId)
    <div x-data>
        <template x-teleport="body">
            {{-- Overlay --}}
            <div
                class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50"
                style="top: 0; left: 0; right: 0; bottom: 0; margin: 0;"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                {{-- Modal Card --}}
                <div
                    class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-[300px] sm:max-w-sm flex flex-col max-h-[95vh] sm:max-h-[90vh] overflow-hidden mx-2 sm:mx-4"
                    @click.stop
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 sm:px-5 sm:py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Preview Nota
                        </h3>
                        <button
                            type="button"
                            wire:click="closeNotaModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Content (iframe) --}}
                    <div class="flex-1 overflow-y-auto overflow-x-hidden bg-gray-100 dark:bg-gray-800 p-3 flex justify-center min-h-0">
                        <iframe
                            id="nota-iframe"
                            src="{{ route('nota.print', $lastTransaksiId) }}"
                            class="w-full max-w-[260px] bg-white border border-gray-200 rounded"
                            style="height: 65vh; min-height: 300px;"
                        ></iframe>
                    </div>

                    {{-- Footer --}}
                    <div class="flex gap-2 px-4 py-3 sm:px-5 sm:py-4 border-t border-gray-200 dark:border-gray-700 shrink-0">
                        <button
                            type="button"
                            wire:click="closeNotaModal"
                            class="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 sm:px-4 sm:py-2.5 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                        >
                            Tutup
                        </button>
                        <button
                            type="button"
                            onclick="document.getElementById('nota-iframe').contentWindow.print()"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-3 py-2 sm:px-4 sm:py-2.5 text-xs sm:text-sm font-semibold text-white hover:bg-primary-500 transition"
                        >
                            Cetak Struk
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
</x-filament-panels::page>