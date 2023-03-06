<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function transactionBalance(Request $req)
    {
        //param : tipe_transaksi,id_transaksi,tanggal,ref_id,catatan,target(supplier/customer),dpp,ppn,uang_muka,biaya,payment_status
        $customerId = null;
        $supplierId = null;
        $paymentMethod = '';
        $payment = 0;
        $remaining = 0;
        $total = (handleNull($req->dpp) + handleNull($req->ppn) - handleNull($req->uang_muka) + handleNull($req->biaya));
        if ($req->payment_method == '1') {
            $payment = $total;
            $remaining = 0;
        } else {
            $payment = handleNull($req->bayar);
            $remaining = $total - $payment;
        }

        if (in_array($req->tipe_transaksi, ['Penjualan', 'Retur Penjualan'])) {
            $customerId = $req->target;
        } else if (in_array($req->tipe_transaksi, ['Pembelian', 'Retur Pembelian'])) {
            $supplierId = $req->target;
        } else {
            return response()->json([
                "result" => false,
                "message" => "Tipe transaksi tidak ditemukan",
            ], 500);
        }

        try {
            DB::beginTransaction();
            DB::table('saldo_transaksi')->insert([
                'tipe_transaksi' => $req->tipe_transaksi,
                'id_transaksi' => $req->id_transaksi,
                'tanggal' => $req->tanggal,
                'ref_id' => $req->ref_id,
                'catatan' => $req->catatan,
                'id_pelanggan' => $customerId,
                'id_pemasok' => $supplierId,
                'dpp' => handleNull($req->dpp),
                'ppn' => handleNull($req->ppn),
                'uang_muka' => handleNull($req->uang_muka),
                'biaya' => handleNull($req->biaya),
                'total' => $total,
                'bayar' => $payment,
                'sisa' => $remaining,
            ]);

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                "result" => false,
                "message" => "Data gagal disimpan",
            ], 500);
        }
    }
}
