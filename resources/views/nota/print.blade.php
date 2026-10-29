<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<tr>
    <td>No</td>
    <td>: {{ $transaksi->kode_transaksi }}</td>
</tr>
<style>
    @php
        $lebar     = $tenant->ukuran_kertas_struk === '58mm' ? '58mm' : '80mm';
        $fontSize  = $tenant->ukuran_kertas_struk === '58mm' ? '10px' : '11px';
    @endphp

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        background: #e5e7eb;
    }

    body {
        width: {{ $lebar }};
        margin: 0 auto;
        font-family: 'Courier New', Courier, monospace;
        font-size: {{ $fontSize }};
        color: #000;
        background: #fff;
        padding: 8px 6px;
    }

    .center   { text-align: center; }
    .right    { text-align: right; }
    .bold     { font-weight: 700; }
    .small    { font-size: 0.85em; }

    .logo {
        max-width: 45px;
        max-height: 45px;
        margin: 0 auto 4px;
        display: block;
    }

    .divider {
        border-top: 1px dashed #000;
        margin: 6px 0;
    }

    table { width: 100%; border-collapse: collapse; }
    td { padding: 1px 0; vertical-align: top; }

    .item-row td { padding-top: 3px; }
    .item-name { display: block; }
    .item-detail { display: block; font-size: 0.9em; }

    .totals td { padding: 2px 0; }

    @media print {
        html, body { background: #fff; }
        @page {
            size: {{ $lebar }} auto;
            margin: 0;
        }
        body {
            width: {{ $lebar }};
            padding: 4px 4px;
        }
    }
</style>
</head>
<body>

    {{-- HEADER TOKO --}}
    <div class="center">
        @if($tenant->logo)
            <img src="{{ Storage::url($tenant->logo) }}" class="logo" alt="logo">
        @endif
        <div class="bold">{{ $tenant->nama }}</div>
        @if($tenant->alamat)
            <div class="small">{{ $tenant->alamat }}</div>
        @endif
        @if($tenant->no_hp)
            <div class="small">{{ $tenant->no_hp }}</div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- INFO TRANSAKSI --}}
    <table class="small">
        <tr>
            <td>No</td>
            <td>: {{ $transaksi->kode ?? $transaksi->id }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ \Illuminate\Support\Carbon::parse($transaksi->tgl_transaksi)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $transaksi->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>: {{ $transaksi->pelanggan->nama ?? 'Umum' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- ITEM --}}
    <table>
    @foreach ($transaksi->details as $item)
    <tr class="item-row">
        <td colspan="2">
            <span class="item-name">{{ $item->barang->nama_barang ?? '-' }}</span>
            <span class="item-detail">
                {{ $item->jumlah }} x {{ number_format($item->harga_satuan, 0, ',', '.') }}
                <span class="right" style="float:right;">
                    {{ number_format($item->subtotal_item, 0, ',', '.') }}
                </span>
            </span>
        </td>
    </tr>
@endforeach
    </table>

    <div class="divider"></div>

    {{-- TOTAL --}}
    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaksi->total_diskon > 0)
            <tr>
                <td>Diskon</td>
                <td class="right">- Rp {{ number_format($transaksi->total_diskon, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="bold">
            <td>Total</td>
            <td class="right">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar ({{ strtoupper($transaksi->metode_pembayaran) }})</td>
            <td class="right">Rp {{ number_format($transaksi->nominal_uang, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="right">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- FOOTER --}}
    <div class="center small">
        @if($tenant->catatan_struk)
            <p>{{ $tenant->catatan_struk }}</p>
        @endif
        <p class="bold" style="margin-top:4px;">Terima Kasih</p>
    </div>

</body>
</html>