<?php

namespace App\Http\Controllers;

use App\TransactionBalance;
use DB;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function transactionBalance(Request $req)
    {
        //param : tipe_transaksi,id_transaksi,tanggal,ref_id,catatan,target(supplier/customer),dpp,ppn,uang_muka,biaya,payment_status
        $data = TransactionBalance::where('id_transaksi', $req->id_transaksi)->where('tipe_transaksi', $req->tipe_transaksi)->first();

        $total = (handleNull($req->dpp) + handleNull($req->ppn) - handleNull($req->uang_muka) + handleNull($req->biaya));
        $array = [
            'tanggal' => $req->tanggal,
            'tipe_pembayaran' => $req->tipe_pembayaran,
            'ref_id' => $req->ref_id,
            'catatan' => $req->catatan,
            'id_pelanggan' => $req->id_pelanggan,
            'id_pemasok' => $req->id_pemasok,
            'dpp' => handleNull($req->dpp),
            'ppn' => handleNull($req->ppn),
            'uang_muka' => handleNull($req->uang_muka),
            'biaya' => handleNull($req->biaya),
            'total' => $total,
        ];

        $newTipePembayaran = $req->tipe_pembayaran;
        if (!$data) {
            $data = new transactionBalance;
            $payment = $newTipePembayaran == '1' ? $total : 0;
            $remaining = $newTipePembayaran == '1' ? 0 : ($total - $payment);

            $array['tipe_transaksi'] = $req->tipe_transaksi;
            $array['id_transaksi'] = $req->id_transaksi;
            $array['bayar'] = $payment;
            $array['sisa'] = $remaining;
        } else {
            $oldTipePembayaran = $data->tipe_pembayaran;

            $array['bayar'] = $payment;
            $array['sisa'] = $remaining;
        }

        $data->fill($array);
        return $data;
        $data->save();
        try {
            DB::beginTransaction();

            $total = (handleNull($req->dpp) + handleNull($req->ppn) - handleNull($req->uang_muka) + handleNull($req->biaya));
            $array = [
                'tanggal' => $req->tanggal,
                'tipe_pembayaran' => $req->tipe_pembayaran,
                'ref_id' => $req->ref_id,
                'catatan' => $req->catatan,
                'id_pelanggan' => $req->id_pelanggan,
                'id_pemasok' => $req->id_pemasok,
                'dpp' => handleNull($req->dpp),
                'ppn' => handleNull($req->ppn),
                'uang_muka' => handleNull($req->uang_muka),
                'biaya' => handleNull($req->biaya),
                'total' => $total,
            ];

            $newTipePembayaran = $req->tipe_pembayaran;
            if (!$data) {
                $payment = $newTipePembayaran == '1' ? $total : 0;
                $remaining = $newTipePembayaran == '1' ? 0 : ($total - $payment);

                $array['tipe_transaksi'] = $req->tipe_transaksi;
                $array['id_transaksi'] = $req->id_transaksi;
                $array['bayar'] = $payment;
                $array['sisa'] = $remaining;
            } else {
                $oldTipePembayaran = $data->tipe_pembayaran;

                $array['bayar'] = $payment;
                $array['sisa'] = $remaining;
            }

            $data->fill((object) $array);
            return $data;
            $data->save();

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
