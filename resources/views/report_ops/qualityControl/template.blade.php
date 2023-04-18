@foreach ($datas as $key => $data)
    <tr>
        <td class="text-center">{{ $key + 1 }}</td>
        <td>{{ $data->nama_pembelian }}</td>
        <td>{{ $data->nama_barang }}</td>
        <td class="text-right">{{ formatNumber($data->jumlah_pembelian_detail) }}</td>
        <td>{{ $data->nama_satuan_barang }}</td>
        <td>{{ $arrayStatus[$data->status_qc] }}</td>
        <td>{{ $data->reason }}</td>
        <td>{{ $data->tanggal_qc }}</td>
        <td class="text-right">{{ formatNumber($data->sg_pembelian_detail) }}</td>
        <td class="text-right">{{ formatNumber($data->be_pembelian_detail) }}</td>
        <td class="text-right">{{ formatNumber($data->ph_pembelian_detail) }}</td>
        <td>{{ $data->warna_pembelian_detail }}</td>
        <td>{{ $data->bentuk_pembelian_detail }}</td>
        <td>{{ $data->keterangan_pembelian_detail }}</td>
    </tr>
@endforeach
