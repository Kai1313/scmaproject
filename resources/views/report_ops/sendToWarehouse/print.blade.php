<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            width: 20cm;
            padding-left: 10px;
            padding-right: 10px;
        }

        @page {
            margin: 3cm 2cm 2cm 2cm;
        }

        .table {
            border-collapse: collapse;
        }

        .table th {
            border-top: 0.2mm solid #000;
            border-bottom: 0.2mm solid #000;
            text-align: center;
            border: 1px solid #000;
            font-size: 12px;
        }

        .table tr td {
            padding: 3px;
            border-bottom: 0.2mm solid #000;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 12px;
        }

        td {
            font-size: 12px;
        }

        h3,
        h4 {
            margin-top: 0px;
            margin-bottom: 0px;
        }
    </style>
</head>

<body>
    @if ($type == 'Rekap')
        <div style="display:flex;margin-bottom:10px;align-items: center;">
            <img src="{{ asset('images/logo2.jpg') }}" style="width:60px;">
            <div style="flex:1;text-align:center;">
                <h3 style="">LAPORAN KIRIM KE GUDANG</h3>
                <span style="font-size:10px;">( {{ strtoupper($type) }} )</span>
            </div>
            <div>
                <table style="width:200px;">
                    <tr>
                        <td style="width:60px;font-weight:bold;">Cabang</td>
                        <td>:</td>
                        <td>{{ $cabang }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Tanggal</td>
                        <td>:</td>
                        <td>{{ $date }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <table class="table" width="100%">
            <thead>
                <tr>
                    <th style="width:30px;">No</th>
                    <th style="width:64px;">Tanggal</th>
                    <th style="width:123px;">Kode Transaksi</th>
                    <th style="width:90px;">Gudang</th>
                    <th style="width:90px;">Gudang Tujuan</th>
                    <th>keterangan</th>
                    <th style="width:100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="text-align:center;">{{ $key + 1 }}</td>
                        <td>{{ $data->tanggal_pindah_barang }}</td>
                        <td>{{ $data->kode_pindah_barang }}</td>
                        <td>{{ $data->gudang->nama_gudang }}</td>
                        <td>{{ $data->gudang2->nama_gudang }}</td>
                        <td>{{ $data->keterangan_pindah_barang }}</td>
                        <td>{{ $arrayStatus[$data->status_pindah_barang] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Detail')
        <div style="display:flex;margin-bottom:10px;align-items: center;">
            <img src="{{ asset('images/logo2.jpg') }}" style="width:60px;">
            <div style="flex:1;text-align:center;">
                <h3 style="">LAPORAN KIRIM KE GUDANG</h3>
                <span style="font-size:10px;">( {{ strtoupper($type) }} )</span>
            </div>
            <div>
                <table style="width:200px;">
                    <tr>
                        <td style="width:60px;font-weight:bold;">Cabang</td>
                        <td>:</td>
                        <td>{{ $cabang }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Tanggal</td>
                        <td>:</td>
                        <td>{{ $date }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @foreach ($datas as $data)
            <div style="border-bottom:1px solid black;padding-bottom:20px;margin-top:20px;">
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
                                <td style="width:120px;">Gudang Tujuan</td>
                                <td style="width:30px;">:</td>
                                <td style="width:200px;">{{ $data->gudang2->nama_gudang }}</td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td>:</td>
                                <td>{{ $data->keterangan_pindah_barang }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>:</td>
                                <td>{{ $arrayStatus[$data->status_pindah_barang] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <table class="table" width="100%">
                    <thead>
                        <tr>
                            <th style="width:30px;">No</th>
                            <th>QR Code</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Batch</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->details as $key => $detail)
                            <tr>
                                <td style="text-align:center;">{{ $key + 1 }}</td>
                                <td>{{ $detail->qr_code }}</td>
                                <td>{{ $detail->barang->nama_barang }}</td>
                                <td style="text-align:right;">{{ formatNumber($detail->qty) }}
                                    {{ $detail->satuan->nama_satuan_barang }}</td>
                                <td style="text-align:center;">{{ $detail->batch }}</td>
                                <td>{{ $arrayStatus[$detail->status_diterima] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @elseif($type == 'Outstanding')
        <div style="display:flex;margin-bottom:10px;align-items: center;">
            <img src="{{ asset('images/logo2.jpg') }}" style="width:60px;">
            <div style="flex:1;text-align:center;">
                <h3 style="">LAPORAN KIRIM KE GUDANG</h3>
                <span style="font-size:10px;">( {{ strtoupper($type) }} )</span>
            </div>
            <div>
                <table style="width:200px;">
                    <tr>
                        <td style="width:60px;font-weight:bold;">Cabang</td>
                        <td>:</td>
                        <td>{{ $cabang }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Tanggal</td>
                        <td>:</td>
                        <td>{{ $date }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <table class="table" width="100%">
            <thead>
                <tr>
                    <th style="width:30px;">No</th>
                    <th style="width:64px;">Tanggal</th>
                    <th style="width:123px;">Kode Transaksi</th>
                    <th style="width:90px;">Gudang</th>
                    <th style="width:90px;">Gudang Tujuan</th>
                    <th style="width:70px;">QR Code</th>
                    <th>Nama Barang</th>
                    <th style="width:60px;">Jumlah</th>
                    <th style="width:100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="text-align:center;">{{ $key + 1 }}</td>
                        <td>{{ $data->parent->tanggal_pindah_barang }}</td>
                        <td>{{ $data->parent->kode_pindah_barang }}</td>
                        <td>{{ $data->parent->gudang->nama_gudang }}</td>
                        <td>{{ $data->parent->gudang2->nama_gudang }}</td>
                        <td>{{ $data->qr_code }}</td>
                        <td>{{ $data->barang->nama_barang }}</td>
                        <td style="text-align:right;">{{ formatNumber($data->qty) }}
                            {{ $data->satuan->nama_satuan_barang }}</td>
                        <td>{{ $arrayStatus[$data->status_diterima] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
<script>
    window.print()
    window.addEventListener('afterprint', (e) => {
        window.close()
    })
</script>

</html>
