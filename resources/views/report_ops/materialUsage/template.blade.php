@if ($type == 'Rekap')
    <div class="table-responsive">
        <table class="table table-bordered data-table display responsive nowrap" width="100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="text-align:center;">{{ $key + 1 }}</td>
                        <td>{{ $data->tanggal }}</td>
                        <td>{{ $data->kode_pemakaian }}</td>
                        <td>{{ $data->cabang->nama_cabang }}</td>
                        <td>{{ $data->gudang->nama_gudang }}</td>
                        <td>{{ $data->catatan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($type == 'Detail')
    @foreach ($datas as $data)
        <div style="border-bottom:1px solid black;">
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <div>
                    <table>
                        <tr>
                            <td style="width:120px;">Cabang</td>
                            <td style="width:30px;">:</td>
                            <td style="width:200px">{{ $data->cabang->nama_cabang }}</td>
                        </tr>
                        <tr>
                            <td>Gudang</td>
                            <td>:</td>
                            <td>{{ $data->gudang->nama_gudang }}</td>
                        </tr>
                        <tr>
                            <td>Kode Transaksi</td>
                            <td>:</td>
                            <td>{{ $data->kode_pemakaian }}</td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table>
                        <tr>
                            <td style="width:120px;">Tanggal</td>
                            <td style="width:30px;">:</td>
                            <td style="width:200px">{{ $data->tanggal }}</td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td>:</td>
                            <td>{{ $data->catatan }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered data-table display responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>QR Code</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Jumlah Zak</th>
                            <th>Berat Zak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->details as $key => $detail)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $detail->kode_batang }}</td>
                                <td>{{ $detail->barang->nama_barang }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->jumlah) }}
                                    {{ $detail->satuan->nama_satuan_barang }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->jumlah_zak) }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->weight_zak) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif
