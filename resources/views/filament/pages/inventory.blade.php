<x-filament-panels::page>
    @php
        $placeholder = asset('images/placeholder.png');

        $rupiah  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $fotoUrl = fn ($b) => $b->gambarUtama?->path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($b->gambarUtama->path)
            : $placeholder;
        $editUrl = fn ($b) => \App\Filament\Resources\BarangResource::getUrl('edit', ['record' => $b]);

        $cards = [
            ['key' => 'all',     'label' => 'Total Barang', 'value' => $stats['total'],   'hint' => $stats['aktif'] . ' barang aktif',
             'icon' => 'heroicon-o-cube',                 'g' => 'indigo'],
            ['key' => 'aman',    'label' => 'Stok Aman',    'value' => $stats['aman'],    'hint' => 'Di atas stok minimal',
             'icon' => 'heroicon-o-check-badge',          'g' => 'emerald'],
            ['key' => 'menipis', 'label' => 'Stok Menipis', 'value' => $stats['menipis'], 'hint' => 'Perlu segera restock',
             'icon' => 'heroicon-o-exclamation-triangle', 'g' => 'amber'],
            ['key' => 'habis',   'label' => 'Stok Habis',   'value' => $stats['habis'],   'hint' => 'Stok kosong',
             'icon' => 'heroicon-o-x-circle',             'g' => 'rose'],
        ];

        $pills = [
            'all'      => ['Semua',    $stats['total']],
            'aman'     => ['Aman',     $stats['aman']],
            'menipis'  => ['Menipis',  $stats['menipis']],
            'habis'    => ['Habis',    $stats['habis']],
            'nonaktif' => ['Nonaktif', $stats['nonaktif']],
        ];
    @endphp

    <style>
        /* ============ TOKENS ============ */
        .inv {
            --card: #ffffff;
            --line: rgba(15, 23, 42, .08);
            --text: #0f172a;
            --muted: #64748b;
            --faint: #94a3b8;
            --soft: #f1f5f9;
            --field: #f8fafc;
            --pri: rgb(var(--primary-600));
            --pri-soft: rgb(var(--primary-50));
            --pri-text: rgb(var(--primary-700));
            --shadow: 0 1px 2px rgba(15, 23, 42, .06);
            --shadow-lg: 0 14px 30px -10px rgba(15, 23, 42, .25);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .dark .inv {
            --card: rgb(var(--gray-900));
            --line: rgba(255, 255, 255, .1);
            --text: #ffffff;
            --muted: #9ca3af;
            --faint: #6b7280;
            --soft: rgba(255, 255, 255, .06);
            --field: rgba(255, 255, 255, .05);
            --pri-soft: rgba(var(--primary-500), .14);
            --pri-text: rgb(var(--primary-400));
            --shadow: none;
            --shadow-lg: 0 14px 30px -10px rgba(0, 0, 0, .6);
        }

        /* tone stok */
        .inv-t-ok     { --t: #059669; --t-bg: #ecfdf5; --t-ring: rgba(5,150,105,.25); --t-bar: #10b981; }
        .inv-t-warn   { --t: #d97706; --t-bg: #fffbeb; --t-ring: rgba(217,119,6,.25); --t-bar: #f59e0b; }
        .inv-t-danger { --t: #e11d48; --t-bg: #fff1f2; --t-ring: rgba(225,29,72,.25); --t-bar: #f43f5e; }
        .dark .inv-t-ok     { --t: #34d399; --t-bg: rgba(16,185,129,.12); --t-ring: rgba(52,211,153,.25); }
        .dark .inv-t-warn   { --t: #fbbf24; --t-bg: rgba(245,158,11,.12); --t-ring: rgba(251,191,36,.25); }
        .dark .inv-t-danger { --t: #fb7185; --t-bg: rgba(244,63,94,.12);  --t-ring: rgba(251,113,133,.25); }

        /* gradient kartu statistik */
        .inv-g-indigo  { --g1: #6366f1; --g2: #8b5cf6; }
        .inv-g-emerald { --g1: #10b981; --g2: #14b8a6; }
        .inv-g-amber   { --g1: #fbbf24; --g2: #f97316; }
        .inv-g-rose    { --g1: #f43f5e; --g2: #ec4899; }

        /* ============ UTIL ============ */
        .inv-ico   { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
        .inv-ico-sm { width: 1rem; height: 1rem; flex-shrink: 0; }
        .inv-ico-lg { width: 1.5rem; height: 1.5rem; flex-shrink: 0; }
        .inv-panel {
            background: var(--card);
            border-radius: 1rem;
            box-shadow: var(--shadow), 0 0 0 1px var(--line);
        }

        /* ============ STAT CARDS ============ */
        .inv-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        @media (min-width: 1024px) { .inv-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (min-width: 1280px) { .inv-stats { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

        .inv-stat {
            position: relative; overflow: hidden; text-align: left; cursor: pointer;
            padding: 1.25rem; border: 0; font: inherit;
            background: var(--card); border-radius: 1rem;
            box-shadow: var(--shadow), 0 0 0 1px var(--line);
            transition: transform .2s, box-shadow .2s;
        }
        .inv-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg), 0 0 0 1px var(--line); }
        .inv-stat.is-active { box-shadow: var(--shadow), 0 0 0 2px var(--g1); }
        .inv-stat-blob {
            position: absolute; top: -2rem; right: -2rem; width: 7rem; height: 7rem; border-radius: 9999px;
            background: linear-gradient(135deg, var(--g1), var(--g2)); opacity: .12;
            transition: transform .3s, opacity .3s;
        }
        .inv-stat:hover .inv-stat-blob { transform: scale(1.25); opacity: .22; }
        .inv-stat-top { position: relative; display: flex; justify-content: space-between; align-items: flex-start; }
        .inv-stat-label { font-size: .875rem; font-weight: 500; color: var(--muted); margin: 0; }
        .inv-stat-value { font-size: 1.875rem; line-height: 1; font-weight: 700; letter-spacing: -.02em; color: var(--text); margin: .6rem 0 0; }
        .inv-stat-icon {
            display: flex; align-items: center; justify-content: center;
            width: 2.75rem; height: 2.75rem; border-radius: .75rem; color: #fff;
            background: linear-gradient(135deg, var(--g1), var(--g2));
            box-shadow: 0 8px 16px -6px var(--g1);
        }
        .inv-stat-hint { position: relative; margin: .75rem 0 0; font-size: .75rem; color: var(--muted); }

        .inv-worth {
            position: relative; overflow: hidden; padding: 1.25rem; border-radius: 1rem; color: #fff;
            grid-column: span 2;
            background: linear-gradient(135deg, #111827, #1f2937 55%, #374151);
        }
        @media (min-width: 1024px) { .inv-worth { grid-column: span 4; } }
        @media (min-width: 1280px) { .inv-worth { grid-column: span 1; } }
        .inv-worth::before, .inv-worth::after { content: ""; position: absolute; border-radius: 9999px; background: rgba(255,255,255,.08); }
        .inv-worth::before { width: 8rem; height: 8rem; right: -2.5rem; bottom: -2.5rem; }
        .inv-worth::after  { width: 4rem; height: 4rem; right: -.5rem; top: 2.5rem; background: rgba(255,255,255,.05); }
        .inv-worth-in { position: relative; }
        .inv-worth-label { display: flex; align-items: center; gap: .5rem; font-size: .875rem; font-weight: 500; color: #d1d5db; }
        .inv-worth-value { margin: .6rem 0 0; font-size: 1.5rem; font-weight: 700; letter-spacing: -.02em; word-break: break-word; }
        .inv-worth-hint { margin: .75rem 0 0; font-size: .75rem; color: #9ca3af; }

        /* ============ TOOLBAR ============ */
        .inv-toolbar { padding: 1rem; }
        .inv-toolbar-row { display: flex; flex-direction: column; gap: .75rem; }
        @media (min-width: 1024px) { .inv-toolbar-row { flex-direction: row; align-items: center; } }

        .inv-search { position: relative; flex: 1; }
        .inv-search-ico { position: absolute; top: 0; bottom: 0; left: .875rem; display: flex; align-items: center; color: var(--faint); pointer-events: none; }
        .inv-field {
            display: block; width: 100%; box-sizing: border-box;
            padding: .625rem .875rem; font-size: .875rem; color: var(--text);
            background-color: var(--field); border: 0; border-radius: .75rem;
            box-shadow: 0 0 0 1px var(--line); outline: none; transition: box-shadow .15s;
        }
        .inv-field::placeholder { color: var(--faint); }
        .inv-field:focus { box-shadow: 0 0 0 2px var(--pri); }
        .inv-search .inv-field { padding-left: 2.75rem; }
        @media (min-width: 1024px) { .inv-select { width: 13rem; } }
        .inv-select option { color: #0f172a; background: #fff; }

        .inv-toggle { display: inline-flex; padding: .25rem; gap: .125rem; background: var(--soft); border-radius: .75rem; align-self: flex-start; }
        .inv-toggle button {
            padding: .5rem; border: 0; border-radius: .5rem; background: transparent;
            color: var(--muted); cursor: pointer; transition: all .15s; display: flex;
        }
        .inv-toggle button:hover { color: var(--text); }
        .inv-toggle button.is-on { background: var(--card); color: var(--pri); box-shadow: 0 1px 3px rgba(0,0,0,.15); }
        .dark .inv-toggle button.is-on { color: var(--pri-text); }

        .inv-pills { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: 1rem; }
        .inv-pill {
            display: inline-flex; align-items: center; gap: .5rem; padding: .375rem .875rem;
            font-size: .875rem; font-weight: 500; border: 0; border-radius: 9999px; cursor: pointer;
            background: var(--soft); color: var(--muted); transition: all .15s;
        }
        .inv-pill:hover { color: var(--text); }
        .inv-pill.is-on { background: var(--pri); color: #fff; box-shadow: 0 6px 14px -4px var(--pri); }
        .inv-pill-count { padding: .0625rem .5rem; font-size: .75rem; border-radius: 9999px; background: var(--card); color: var(--muted); }
        .inv-pill.is-on .inv-pill-count { background: rgba(255,255,255,.22); color: #fff; }
        .inv-reset {
            margin-left: auto; display: inline-flex; align-items: center; gap: .25rem;
            font-size: .875rem; font-weight: 500; color: var(--muted); background: none; border: 0; cursor: pointer;
        }
        .inv-reset:hover { color: #e11d48; }

        /* ============ HASIL ============ */
        .inv-results { transition: opacity .2s; }
        .inv-results.is-loading { opacity: .6; }

        /* Empty state */
        .inv-empty {
            display: flex; flex-direction: column; align-items: center; text-align: center;
            padding: 4rem 1.5rem; background: var(--card); border: 2px dashed var(--line); border-radius: 1rem;
        }
        .inv-empty-ico { display: flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; border-radius: 9999px; background: var(--soft); color: var(--faint); }
        .inv-empty h3 { margin: 1rem 0 0; font-size: 1rem; font-weight: 600; color: var(--text); }
        .inv-empty p  { margin: .25rem 0 0; max-width: 24rem; font-size: .875rem; color: var(--muted); }
        .inv-empty-actions { display: flex; gap: .5rem; margin-top: 1.25rem; }

        /* Grid */
        .inv-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        @media (min-width: 640px)  { .inv-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .inv-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 1536px) { .inv-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .inv-item {
            display: flex; flex-direction: column; overflow: hidden;
            background: var(--card); border-radius: 1rem;
            box-shadow: var(--shadow), 0 0 0 1px var(--line);
            transition: transform .3s, box-shadow .3s;
        }
        .inv-item:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg), 0 0 0 1px var(--line); }

        .inv-photo { position: relative; aspect-ratio: 4 / 3; overflow: hidden; background: var(--soft); }
        .inv-photo img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .5s; }
        .inv-item:hover .inv-photo img { transform: scale(1.08); }
        .inv-photo img.is-off, .inv-thumb img.is-off { filter: grayscale(1); }

        .inv-badge {
            display: inline-flex; align-items: center; gap: .375rem;
            padding: .25rem .625rem; font-size: .75rem; font-weight: 600; border-radius: 9999px;
            color: var(--t); background: var(--t-bg); box-shadow: inset 0 0 0 1px var(--t-ring);
        }
        .inv-badge i { width: .375rem; height: .375rem; border-radius: 9999px; background: var(--t); display: block; }
        .inv-photo .inv-badge { position: absolute; top: .75rem; left: .75rem; backdrop-filter: blur(6px); }
        .inv-off-tag {
            position: absolute; top: .75rem; right: .75rem; padding: .25rem .625rem;
            font-size: .75rem; font-weight: 600; color: #fff; background: rgba(17,24,39,.8);
            border-radius: 9999px; backdrop-filter: blur(6px);
        }

        .inv-body { display: flex; flex-direction: column; flex: 1; padding: 1rem; }
        .inv-meta { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
        .inv-code {
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
            padding: .125rem .5rem; font: 500 .75rem ui-monospace, SFMono-Regular, Menlo, monospace;
            color: var(--muted); background: var(--soft); border-radius: .375rem;
        }
        .inv-cat { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .75rem; font-weight: 500; color: var(--pri-text); }
        .inv-name {
            margin: .5rem 0 0; min-height: 2.5rem; font-size: .875rem; line-height: 1.25rem; font-weight: 600; color: var(--text);
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .inv-price { margin: .5rem 0 0; font-size: 1.125rem; font-weight: 700; color: var(--text); }
        .inv-price small { font-size: .75rem; font-weight: 400; color: var(--faint); }

        .inv-stock { margin-top: 1rem; }
        .inv-stock-row { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: .375rem; font-size: .75rem; color: var(--muted); }
        .inv-stock-row b { font-weight: 600; color: var(--t); }
        .inv-stock-row b span { font-weight: 400; color: var(--faint); }
        .inv-bar { height: .5rem; overflow: hidden; border-radius: 9999px; background: var(--soft); }
        .inv-bar > div { height: 100%; border-radius: 9999px; background: var(--t-bar); transition: width .5s; }
        .inv-spacer { flex: 1; min-height: 1rem; }

        .inv-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; box-sizing: border-box;
            padding: .5rem 1rem; font-size: .875rem; font-weight: 500; text-decoration: none;
            color: var(--text); background: var(--field); border-radius: .75rem;
            box-shadow: inset 0 0 0 1px var(--line); transition: all .15s;
        }
        .inv-btn:hover { background: var(--pri); color: #fff; box-shadow: inset 0 0 0 1px var(--pri); }

        /* List / tabel */
        .inv-tablewrap { overflow-x: auto; }
        .inv-table { width: 100%; min-width: 760px; border-collapse: collapse; }
        .inv-table th {
            padding: .75rem 1.25rem; text-align: left; font-size: .75rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .05em; color: var(--muted); background: var(--soft);
        }
        .inv-table td { padding: .75rem 1.25rem; border-top: 1px solid var(--line); vertical-align: middle; }
        .inv-table tbody tr { transition: background .15s; }
        .inv-table tbody tr:hover { background: var(--soft); }
        .inv-right { text-align: right !important; }

        .inv-prod { display: flex; align-items: center; gap: .75rem; }
        .inv-thumb { width: 3rem; height: 3rem; flex-shrink: 0; overflow: hidden; border-radius: .75rem; background: var(--soft); box-shadow: 0 0 0 1px var(--line); }
        .inv-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .inv-prod-name { margin: 0; max-width: 18rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .875rem; font-weight: 600; color: var(--text); }
        .inv-prod-code { margin: 0; font: 400 .75rem ui-monospace, SFMono-Regular, Menlo, monospace; color: var(--muted); }
        .inv-chip { display: inline-flex; padding: .25rem .625rem; font-size: .75rem; font-weight: 500; border-radius: 9999px; color: var(--pri-text); background: var(--pri-soft); }
        .inv-money { white-space: nowrap; font-size: .875rem; font-weight: 500; color: var(--text); }
        .inv-mini { width: 10rem; }
        .inv-mini .inv-stock-row { margin-bottom: 0; }
        .inv-mini .inv-bar { height: .375rem; margin-top: .375rem; }
        .inv-status { display: flex; flex-direction: column; align-items: flex-start; gap: .25rem; }
        .inv-status small { font-size: .75rem; color: var(--faint); }
        .inv-link {
            display: inline-flex; align-items: center; gap: .375rem; padding: .375rem .75rem;
            font-size: .875rem; font-weight: 500; text-decoration: none; color: var(--pri-text); border-radius: .5rem; transition: background .15s;
        }
        .inv-link:hover { background: var(--pri-soft); }

        .inv-pager { margin-top: 1.5rem; padding: .75rem; }
    </style>

    <div class="inv">

        {{-- ==================== STAT CARDS ==================== --}}
        <div class="inv-stats">
            @foreach ($cards as $c)
                <button type="button"
                        wire:click="$set('status', '{{ $c['key'] }}')"
                        class="inv-stat inv-g-{{ $c['g'] }} {{ $status === $c['key'] ? 'is-active' : '' }}">
                    <div class="inv-stat-blob"></div>
                    <div class="inv-stat-top">
                        <div>
                            <p class="inv-stat-label">{{ $c['label'] }}</p>
                            <p class="inv-stat-value">{{ number_format($c['value'], 0, ',', '.') }}</p>
                        </div>
                        <span class="inv-stat-icon">
                            <x-filament::icon :icon="$c['icon']" class="inv-ico-lg" />
                        </span>
                    </div>
                    <p class="inv-stat-hint">{{ $c['hint'] }}</p>
                </button>
            @endforeach

            <div class="inv-worth">
                <div class="inv-worth-in">
                    <div class="inv-worth-label">
                        <x-filament::icon icon="heroicon-o-banknotes" class="inv-ico" />
                        Nilai Stok
                    </div>
                    <p class="inv-worth-value">{{ $rupiah($stats['nilai']) }}</p>
                    <p class="inv-worth-hint">Stok × harga jual</p>
                </div>
            </div>
        </div>

        {{-- ==================== TOOLBAR ==================== --}}
        <div class="inv-panel inv-toolbar">
            <div class="inv-toolbar-row">
                <div class="inv-search">
                    <span class="inv-search-ico">
                        <x-filament::icon icon="heroicon-m-magnifying-glass" class="inv-ico" />
                    </span>
                    <input type="search" class="inv-field"
                           wire:model.live.debounce.400ms="search"
                           placeholder="Cari nama atau kode barang…">
                </div>

                <select wire:model.live="kategori" class="inv-field inv-select">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoris as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>

                <select wire:model.live="sort" class="inv-field inv-select">
                    <option value="nama_asc">Nama A → Z</option>
                    <option value="nama_desc">Nama Z → A</option>
                    <option value="stok_asc">Stok terendah</option>
                    <option value="stok_desc">Stok tertinggi</option>
                    <option value="harga_asc">Harga termurah</option>
                    <option value="harga_desc">Harga termahal</option>
                    <option value="terbaru">Terbaru</option>
                </select>

                <div class="inv-toggle">
                    <button type="button" wire:click="$set('tampilan', 'grid')" title="Tampilan grid"
                            class="{{ $tampilan === 'grid' ? 'is-on' : '' }}">
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="inv-ico" />
                    </button>
                    <button type="button" wire:click="$set('tampilan', 'list')" title="Tampilan list"
                            class="{{ $tampilan === 'list' ? 'is-on' : '' }}">
                        <x-filament::icon icon="heroicon-o-list-bullet" class="inv-ico" />
                    </button>
                </div>
            </div>

            <div class="inv-pills">
                @foreach ($pills as $key => [$label, $count])
                    <button type="button" wire:click="$set('status', '{{ $key }}')"
                            class="inv-pill {{ $status === $key ? 'is-on' : '' }}">
                        {{ $label }}
                        <span class="inv-pill-count">{{ $count }}</span>
                    </button>
                @endforeach

                @if ($search !== '' || $kategori || $status !== 'all')
                    <button type="button" wire:click="resetFilter" class="inv-reset">
                        <x-filament::icon icon="heroicon-m-x-mark" class="inv-ico-sm" />
                        Reset filter
                    </button>
                @endif
            </div>
        </div>

        {{-- ==================== HASIL ==================== --}}
        <div class="inv-results" wire:loading.class="is-loading">

            @if ($barangs->isEmpty())
                <div class="inv-empty">
                    <span class="inv-empty-ico">
                        <x-filament::icon icon="heroicon-o-archive-box-x-mark" class="inv-ico-lg" />
                    </span>
                    <h3>Barang tidak ditemukan</h3>
                    <p>Coba ubah kata kunci atau filter, atau tambahkan barang baru ke inventory kamu.</p>
                    <div class="inv-empty-actions">
                        <x-filament::button color="gray" wire:click="resetFilter">Reset filter</x-filament::button>
                        <x-filament::button tag="a" :href="\App\Filament\Resources\BarangResource::getUrl('create')" icon="heroicon-m-plus">
                            Tambah Barang
                        </x-filament::button>
                    </div>
                </div>

            @elseif ($tampilan === 'grid')
                <div class="inv-grid">
                    @foreach ($barangs as $barang)
                        @php $meta = $this->stokMeta($barang); @endphp

                        <div wire:key="grid-{{ $barang->id }}" class="inv-item inv-t-{{ $meta['tone'] }}">
                            <div class="inv-photo">
                                <img src="{{ $fotoUrl($barang) }}"
                                     alt="{{ $barang->nama_barang }}" loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                     class="{{ $barang->is_active ? '' : 'is-off' }}">

                                <span class="inv-badge"><i></i>{{ $meta['label'] }}</span>

                                @unless ($barang->is_active)
                                    <span class="inv-off-tag">Nonaktif</span>
                                @endunless
                            </div>

                            <div class="inv-body">
                                <div class="inv-meta">
                                    <span class="inv-code">{{ $barang->kode_barang }}</span>
                                    <span class="inv-cat">{{ $barang->kategori?->nama_kategori ?? '—' }}</span>
                                </div>

                                <h3 class="inv-name">{{ $barang->nama_barang }}</h3>

                                <p class="inv-price">
                                    {{ $rupiah($barang->harga_jual) }}
                                    <small>/ {{ $barang->satuan }}</small>
                                </p>

                                <div class="inv-stock">
                                    <div class="inv-stock-row">
                                        <span>Stok</span>
                                        <b>{{ number_format($barang->stok, 0, ',', '.') }} {{ $barang->satuan }}
                                            <span>/ min {{ $barang->stok_minimal }}</span></b>
                                    </div>
                                    <div class="inv-bar"><div style="width: {{ $meta['pct'] }}%"></div></div>
                                </div>

                                <div class="inv-spacer"></div>

                                <a href="{{ $editUrl($barang) }}" class="inv-btn">
                                    <x-filament::icon icon="heroicon-m-pencil-square" class="inv-ico-sm" />
                                    Edit / Update Stok
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <div class="inv-panel" style="overflow:hidden">
                    <div class="inv-tablewrap">
                        <table class="inv-table">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                    <th class="inv-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($barangs as $barang)
                                    @php $meta = $this->stokMeta($barang); @endphp
                                    <tr wire:key="list-{{ $barang->id }}" class="inv-t-{{ $meta['tone'] }}">
                                        <td>
                                            <div class="inv-prod">
                                                <div class="inv-thumb">
                                                    <img src="{{ $fotoUrl($barang) }}" alt="" loading="lazy"
                                                         onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                                         class="{{ $barang->is_active ? '' : 'is-off' }}">
                                                </div>
                                                <div style="min-width:0">
                                                    <p class="inv-prod-name">{{ $barang->nama_barang }}</p>
                                                    <p class="inv-prod-code">{{ $barang->kode_barang }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="inv-chip">{{ $barang->kategori?->nama_kategori ?? '—' }}</span></td>
                                        <td class="inv-money">{{ $rupiah($barang->harga_jual) }}</td>
                                        <td>
                                            <div class="inv-mini">
                                                <div class="inv-stock-row">
                                                    <b>{{ number_format($barang->stok, 0, ',', '.') }} {{ $barang->satuan }}</b>
                                                    <span style="color:var(--faint)">min {{ $barang->stok_minimal }}</span>
                                                </div>
                                                <div class="inv-bar"><div style="width: {{ $meta['pct'] }}%"></div></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="inv-status">
                                                <span class="inv-badge"><i></i>{{ $meta['label'] }}</span>
                                                @unless ($barang->is_active)
                                                    <small>Nonaktif</small>
                                                @endunless
                                            </div>
                                        </td>
                                        <td class="inv-right">
                                            <a href="{{ $editUrl($barang) }}" class="inv-link">
                                                <x-filament::icon icon="heroicon-m-pencil-square" class="inv-ico-sm" />
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($barangs->hasPages())
                <div class="inv-panel inv-pager">
                    <x-filament::pagination :paginator="$barangs" />
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>