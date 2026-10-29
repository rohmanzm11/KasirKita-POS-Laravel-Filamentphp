<x-filament-panels::page>
    @php
        $r = $this->getRingkasan();
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        $metode = [
            'tunai' => ['label' => 'Tunai', 'class' => 'lp-green'],
            'qris'  => ['label' => 'QRIS',  'class' => 'lp-blue'],
            'debit' => ['label' => 'Debit', 'class' => 'lp-amber'],
        ];
    @endphp

    <style>
        .lp-wrap { display: flex; flex-direction: column; gap: 1rem; }

        /* ---------- Kartu umum ---------- */
        .lp-card {
            background: #fff;
            border: 1px solid rgb(var(--gray-200));
            border-radius: .9rem;
            padding: .85rem 1rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / .04);
        }
        .dark .lp-card {
            background: rgb(var(--gray-900));
            border-color: rgb(255 255 255 / .1);
        }

        /* ---------- Ringkasan ---------- */
        .lp-summary { display: grid; gap: 1rem; grid-template-columns: 1fr; }
        @media (min-width: 768px) { .lp-summary { grid-template-columns: 1.15fr 1fr; } }

        .lp-hero {
            position: relative;
            overflow: hidden;
            border-radius: .9rem;
            padding: 1.25rem 1.4rem;
            color: #fff;
            background: linear-gradient(135deg, rgb(var(--primary-700)), rgb(var(--primary-500)));
            box-shadow: 0 6px 18px -6px rgb(var(--primary-600) / .55);
        }
        .lp-hero::after {
            content: '';
            position: absolute;
            right: -40px; top: -40px;
            width: 160px; height: 160px;
            border-radius: 9999px;
            background: rgb(255 255 255 / .12);
        }
        .lp-eyebrow {
            font-size: .72rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            opacity: .85;
        }
        .lp-amount {
            margin-top: .35rem;
            font-size: 1.9rem;
            line-height: 1.1;
            font-weight: 700;
            letter-spacing: -.01em;
        }
        .lp-meta { margin-top: .6rem; display: flex; flex-wrap: wrap; gap: .4rem; }
        .lp-pill {
            font-size: .72rem;
            padding: .15rem .6rem;
            border-radius: 9999px;
            background: rgb(255 255 255 / .18);
            backdrop-filter: blur(2px);
        }

        /* ---------- Rincian metode ---------- */
        .lp-methods-title {
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: rgb(var(--gray-500));
            margin-bottom: .6rem;
        }
        .lp-method { margin-bottom: .7rem; }
        .lp-method:last-child { margin-bottom: 0; }
        .lp-method-row {
            display: flex; align-items: center; justify-content: space-between;
            font-size: .82rem;
        }
        .lp-method-name { display: flex; align-items: center; gap: .45rem; font-weight: 600; }
        .lp-dot { width: .55rem; height: .55rem; border-radius: 9999px; background: var(--c); }
        .lp-count { font-weight: 400; font-size: .72rem; color: rgb(var(--gray-500)); }
        .lp-value { font-weight: 600; font-variant-numeric: tabular-nums; }
        .lp-bar {
            margin-top: .3rem;
            height: .35rem;
            border-radius: 9999px;
            background: rgb(var(--gray-100));
            overflow: hidden;
        }
        .dark .lp-bar { background: rgb(255 255 255 / .08); }
        .lp-bar > span {
            display: block; height: 100%;
            border-radius: 9999px;
            background: var(--c);
            transition: width .4s ease;
        }
        .lp-green { --c: rgb(var(--success-500)); }
        .lp-blue  { --c: rgb(var(--info-500)); }
        .lp-amber { --c: rgb(var(--warning-500)); }

        /* ---------- Tabel: ringkas & cantik ---------- */
        .lp-table .fi-ta-ctn {
            border-radius: .9rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgb(0 0 0 / .04);
        }
        .lp-table .fi-ta-header-cell {
            padding: .5rem .75rem !important;
            background: rgb(var(--gray-50));
        }
        .dark .lp-table .fi-ta-header-cell { background: rgb(255 255 255 / .04); }
        .lp-table .fi-ta-header-cell span {
            font-size: .68rem !important;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: rgb(var(--gray-500));
        }

        /* rapatkan tinggi baris */
        .lp-table .fi-ta-text {
            padding-top: .3rem !important;
            padding-bottom: .3rem !important;
        }
        .lp-table .fi-ta-cell { line-height: 1.25; }
        .lp-table .fi-ta-text-item-description { font-size: .7rem !important; }

        .lp-table .fi-ta-row { transition: background .15s ease; }
        .lp-table .fi-ta-row:hover { background: rgb(var(--primary-500) / .07); }

        .lp-table .fi-ta-header { padding: .75rem 1rem !important; }
        .lp-table .fi-ta-header-heading { font-size: .95rem; }
        .lp-table .fi-ta-footer-summary td,
        .lp-table .fi-ta-summary-row td { font-size: .82rem; }
    </style>

    <div class="lp-wrap">
        {{-- Filter periode --}}
        <div class="lp-card">
            {{ $this->form }}
        </div>

        {{-- Ringkasan periode --}}
        <div class="lp-summary">
            <div class="lp-hero">
                <div class="lp-eyebrow">Total pemasukan &middot; {{ $this->getPeriodeLabel() }}</div>
                <div class="lp-amount">{{ $rp($r['total']) }}</div>
                <div class="lp-meta">
                    <span class="lp-pill">{{ number_format($r['jumlah'], 0, ',', '.') }} transaksi</span>
                    <span class="lp-pill">Rata-rata {{ $rp($r['rata']) }}</span>
                </div>
            </div>

            <div class="lp-card">
                <div class="lp-methods-title">Berdasarkan metode pembayaran</div>

                @foreach ($metode as $key => $m)
                    @php
                        $row = $r['metode']->get($key);
                        $nilai = (float) ($row->total ?? 0);
                        $pct = $r['total'] > 0 ? round($nilai / $r['total'] * 100) : 0;
                    @endphp

                    <div class="lp-method {{ $m['class'] }}">
                        <div class="lp-method-row">
                            <span class="lp-method-name">
                                <span class="lp-dot"></span>
                                {{ $m['label'] }}
                                <span class="lp-count">{{ (int) ($row->jumlah ?? 0) }} trx &middot; {{ $pct }}%</span>
                            </span>
                            <span class="lp-value">{{ $rp($nilai) }}</span>
                        </div>
                        <div class="lp-bar"><span style="width: {{ $pct }}%"></span></div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tabel --}}
        <div class="lp-table">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>