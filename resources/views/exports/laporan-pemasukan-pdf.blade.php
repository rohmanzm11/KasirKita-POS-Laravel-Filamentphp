<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan</title>
    <style>
        @page { margin: 28px 32px 56px 32px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
            margin: 0;
        }

        /* ---------- Header ---------- */
        .header {
            width: 100%;
            background: #1e3a8a;
            border-radius: 8px;
            color: #fff;
        }
        .header td { padding: 16px 20px; vertical-align: middle; }
        .brand { font-size: 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #bfdbfe; }
        .title { font-size: 20px; font-weight: bold; margin-top: 3px; }
        .periode { font-size: 10px; color: #dbeafe; margin-top: 3px; }
        .header .right { text-align: right; width: 32%; }
        .stamp-label { font-size: 7.5px; color: #93c5fd; text-transform: uppercase; letter-spacing: 1px; }
        .stamp-value { font-size: 9px; margin-top: 2px; }

        /* ---------- Kartu ringkasan ---------- */
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 14px -8px 0 -8px; width: 103%; }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .card-main { background: #eff6ff; border-color: #bfdbfe; }
        .card-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: .8px; color: #64748b; }
        .card-value { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        .card-main .card-value { color: #1d4ed8; font-size: 15px; }
        .card-sub { font-size: 7.5px; color: #94a3b8; margin-top: 2px; }

        /* ---------- Judul seksi ---------- */
        .section {
            margin: 18px 0 6px 0;
            font-size: 10px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: .8px;
            border-left: 3px solid #3b82f6;
            padding-left: 7px;
        }

        /* ---------- Tabel umum ---------- */
        table.data { width: 100%; border-collapse: collapse; }
        table.data thead th {
            background: #1e3a8a;
            color: #fff;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .5px;
            text-align: left;
            padding: 7px 8px;
        }
        table.data tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        table.data tbody tr { page-break-inside: avoid; }
        table.data tbody tr.alt td { background: #f8fafc; }
        table.data tfoot td {
            padding: 8px;
            font-weight: bold;
            background: #eff6ff;
            color: #1e3a8a;
            border-top: 2px solid #3b82f6;
        }
        .r { text-align: right; }
        .c { text-align: center; }
        .mono { font-size: 8.5px; color: #334155; }
        .muted { color: #6b7280; }
        .neg { color: #dc2626; }

        /* ---------- Badge metode ---------- */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9px;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: .3px;
        }
        .b-tunai { background: #dcfce7; color: #15803d; }
        .b-qris  { background: #dbeafe; color: #1d4ed8; }
        .b-debit { background: #fef3c7; color: #b45309; }
        .b-lain  { background: #e5e7eb; color: #374151; }

        /* ---------- Bar persentase ---------- */
        .bar-bg { background: #e5e7eb; height: 6px; border-radius: 3px; width: 100%; }
        .bar-fill { height: 6px; border-radius: 3px; }
        .f-tunai { background: #22c55e; }
        .f-qris  { background: #3b82f6; }
        .f-debit { background: #f59e0b; }
        .f-lain  { background: #9ca3af; }

        /* ---------- Footer tetap di tiap halaman ---------- */
        .footer {
            position: fixed;
            left: 0; right: 0; bottom: -34px;
            height: 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 7.5px;
            color: #9ca3af;
        }
        .footer table { width: 100%; }
        .footer td { padding-top: 6px; }
        .pagenum:before { content: counter(page); }
    </style>
</head>
<body>
    @php
        $rp        = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $total     = (float) $rows->sum('total_bayar');
        $jumlah    = $rows->count();
        $rata      = $jumlah > 0 ? $total / $jumlah : 0;
        $diskon    = (float) $rows->sum('total_diskon');
        $kelas     = fn ($m) => in_array($m, ['tunai', 'qris', 'debit']) ? $m : 'lain';
    @endphp

    {{-- Footer (muncul di setiap halaman) --}}
    <div class="footer">
        <table>
            <tr>
                <td>{{ $toko }} &middot; Laporan Pemasukan</td>
                <td class="r">Halaman <span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

    {{-- Header --}}
    <table class="header">
        <tr>
            <td>
                <div class="brand">{{ $toko }}</div>
                <div class="title">Laporan Pemasukan</div>
                <div class="periode">{{ $label }}</div>
            </td>
            <td class="right">
                <div class="stamp-label">Dicetak</div>
                <div class="stamp-value">{{ now()->translatedFormat('d F Y, H:i') }}</div>
            </td>
        </tr>
    </table>

    {{-- Kartu ringkasan --}}
    <table class="cards">
        <tr>
            <td class="card card-main" width="34%">
                <div class="card-label">Total Pemasukan</div>
                <div class="card-value">{{ $rp($total) }}</div>
                <div class="card-sub">Transaksi selesai</div>
            </td>
            <td class="card" width="22%">
                <div class="card-label">Transaksi</div>
                <div class="card-value">{{ number_format($jumlah, 0, ',', '.') }}</div>
                <div class="card-sub">Jumlah nota</div>
            </td>
            <td class="card" width="22%">
                <div class="card-label">Rata-rata</div>
                <div class="card-value">{{ $rp($rata) }}</div>
                <div class="card-sub">Per transaksi</div>
            </td>
            <td class="card" width="22%">
                <div class="card-label">Total Diskon</div>
                <div class="card-value">{{ $rp($diskon) }}</div>
                <div class="card-sub">Sudah dipotong</div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan metode pembayaran --}}
    <div class="section">Ringkasan Metode Pembayaran</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 20%">Metode</th>
                <th class="r" style="width: 14%">Transaksi</th>
                <th style="width: 30%">Proporsi</th>
                <th class="r" style="width: 36%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows->groupBy('metode_pembayaran') as $metode => $group)
                @php
                    $sub = (float) $group->sum('total_bayar');
                    $pct = $total > 0 ? round($sub / $total * 100) : 0;
                @endphp
                <tr class="{{ $loop->even ? 'alt' : '' }}">
                    <td><span class="badge b-{{ $kelas($metode) }}">{{ strtoupper($metode) }}</span></td>
                    <td class="r">{{ $group->count() }}</td>
                    <td>
                        <table style="width:100%; border-collapse:collapse">
                            <tr>
                                <td style="border:0; padding:0; width:72%">
                                    <div class="bar-bg"><div class="bar-fill f-{{ $kelas($metode) }}" style="width: {{ $pct }}%"></div></div>
                                </td>
                                <td style="border:0; padding:0 0 0 6px" class="muted">{{ $pct }}%</td>
                            </tr>
                        </table>
                    </td>
                    <td class="r"><strong>{{ $rp($sub) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="4" class="c muted">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="r">{{ $jumlah }}</td>
                <td></td>
                <td class="r">{{ $rp($total) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Rincian transaksi --}}
    <div class="section">Rincian Transaksi</div>
    <table class="data">
        <thead>
            <tr>
                <th class="c" style="width: 5%">No</th>
                <th style="width: 22%">Kode</th>
                <th style="width: 17%">Tanggal</th>
                <th style="width: 18%">Kasir</th>
                <th class="c" style="width: 10%">Metode</th>
                <th class="r" style="width: 12%">Diskon</th>
                <th class="r" style="width: 16%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $t)
                <tr class="{{ $loop->even ? 'alt' : '' }}">
                    <td class="c muted">{{ $i + 1 }}</td>
                    <td class="mono">{{ $t->kode_transaksi }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y H:i') }}</td>
                    <td>{{ $t->user?->name }}</td>
                    <td class="c"><span class="badge b-{{ $kelas($t->metode_pembayaran) }}">{{ strtoupper($t->metode_pembayaran) }}</span></td>
                    <td class="r {{ (float) $t->total_diskon > 0 ? 'neg' : 'muted' }}">
                        {{ (float) $t->total_diskon > 0 ? '- ' . number_format($t->total_diskon, 0, ',', '.') : '-' }}
                    </td>
                    <td class="r"><strong>{{ number_format($t->total_bayar, 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="7" class="c muted" style="padding: 14px">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        @if ($rows->count())
            <tfoot>
                <tr>
                    <td colspan="5" class="r">TOTAL</td>
                    <td class="r">{{ number_format($diskon, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>