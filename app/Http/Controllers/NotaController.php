<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;

class NotaController extends Controller
{
   public function print(Transaksi $transaksi)
{
    $transaksi->load(['tenant', 'user', 'pelanggan', 'diskon', 'details.barang']);

    abort_unless(
        auth()->user()->isSuperAdmin() || auth()->user()->tenant_id === $transaksi->tenant_id,
        403
    );

    return view('nota.print', [
        'transaksi' => $transaksi,
        'tenant'    => $transaksi->tenant,
    ]);
}
}