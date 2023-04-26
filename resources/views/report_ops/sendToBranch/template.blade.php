@if ($type == 'Rekap')
    <div class="table-responsive">
        <table class="table table-bordered data-table display responsive nowrap" width="100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kode Pindah Cabang</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Cabang Tujuan</th>
                    <th>keterangan</th>
                    <th>Jasa Pengiriman</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="text-align:center;">{{ $key + 1 }}</td>
                        <td>{{ $data->tanggal_pindah_barang }}</td>
                        <td>{{ $data->kode_pindah_barang }}</td>
                        <td>{{ $data->cabang->nama_cabang }}</td>
                        <td>{{ $data->gudang->nama_gudang }}</td>
                        <td>{{ $data->cabang2->nama_cabang }}</td>
                        <td>{{ $data->keterangan_pindah_barang }}</td>
                        <td>{{ $data->transporter }} {{ $data->nomor_polisi ? '(' . $data->nomor_polisi . ')' : '' }}
                        </td>
                        <td>{{ $arrayStatus[$data->status_pindah_barang] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($type == 'Detail')
    @foreach ($datas as $data)
        <div style="border-bottom:1px solid black;">
            <h4><b>{{ $data->kode_pindah_barang }}</b></h4>
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
                            <td>{{ $data->kode_pindah_barang }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td>{{ $data->tanggal_pindah_barang }}</td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table>
                        <tr>
                            <td style="width:120px;">Cabang Tujuan</td>
                            <td style="width:30px;">:</td>
                            <td style="width:200px;">{{ $data->cabang2->nama_cabang }}</td>
                        </tr>
                        <tr>
                            <td>Jasa Pengiriman</td>
                            <td>:</td>
                            <td>{{ $data->transporter }}
                                {{ $data->nomor_polisi ? '(' . $data->nomor_polisi . ')' : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td>:</td>
                            <td>{{ $data->transporter }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>:</td>
                            <td>{{ $arrayStatus[$data->status_pindah_barang] }}</td>
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
                            <th>Batch</th>
                            <th>Kadaluarsa</th>
                            <th>SG</th>
                            <th>BE</th>
                            <th>PH</th>
                            <th>Bentuk</th>
                            <th>Warna</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->details as $key => $detail)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $detail->qr_code }}</td>
                                <td>{{ $detail->barang->nama_barang }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->qty) }}
                                    {{ $detail->satuan->nama_satuan_barang }}</td>
                                <td>{{ $detail->batch }}</td>
                                <td>{{ $detail->tanggal_kadaluarsa }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->sg) }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->be) }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->ph) }}</td>
                                <td>{{ $detail->bentuk }}</td>
                                <td>{{ $detail->warna }}</td>
                                <td>{{ $detail->keterangan }}</td>
                                <td>{{ $arrayStatus[$detail->status_diterima] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@elseif($type == 'Outstanding')
    <div class="table-responsive">
        <table class="table table-bordered data-table display responsive nowrap" width="100%">
            <thead>
                <tr>

                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kode Pindah Cabang</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Cabang Tujuan</th>
                    <th>QR Code</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="text-align:center;">{{ $key + 1 }}</td>
                        <td>{{ $data->parent->tanggal_pindah_barang }}</td>
                        <td>{{ $data->parent->kode_pindah_barang }}</td>
                        <td>{{ $data->parent->cabang->nama_cabang }}</td>
                        <td>{{ $data->parent->gudang->nama_gudang }}</td>
                        <td>{{ $data->parent->cabang2->nama_cabang }}</td>
                        <td>{{ $data->qr_code }}</td>
                        <td>{{ $data->barang->nama_barang }}</td>
                        <td style="text-align:right;">{{ formatNumber($data->qty) }}
                            {{ $data->satuan->nama_satuan_barang }}</td>
                        <td>{{ $arrayStatus[$data->status_diterima] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
