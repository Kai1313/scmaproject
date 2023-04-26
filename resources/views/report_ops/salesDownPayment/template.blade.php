<div class="table-responsive">
    <table class="table table-bordered data-table display responsive nowrap" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Cabang</th>
                <th>Kode Transaksi</th>
                <th>Nomor SO</th>
                <th>Akun Slip</th>
                <th>Mata Uang</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="text-align:center;">{{ $key + 1 }}</td>
                    <td>{{ $data->tanggal }}</td>
                    <td>{{ $data->cabang->nama_cabang }}</td>
                    <td>{{ $data->kode_uang_muka_penjualan }}</td>
                    <td>{{ $data->salesOrder->nama_permintaan_penjualan }}</td>
                    <td>{{ $data->slip->nama_slip }}</td>
                    <td>{{ $data->mataUang->nama_mata_uang }}</td>
                    <td style="text-align:right;">{{ formatNumber($data->nominal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
