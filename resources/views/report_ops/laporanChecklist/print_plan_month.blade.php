<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .table td {
            border: 0.5px solid black;
            vertical-align: middle;
            padding: 5px;
            /* font-weight: bold; */
        }

        body {
            font-family: Arial;
            font-size: 13px;
            width: 1020px;
        }

        .table {
            /* width: 718px; */
            border-collapse: collapse;
            width: 100%;
        }

        .table-no-border {
            border: 0 !important;
        }

        .header {
            text-align: center;
            font-weight: bold;
        }

        .left {
            text-align: left;
        }

        .no-border-horizontal td {
            border-top: none;
            border-bottom: none;
        }

        .right-white {
            border-right: 1px solid white !important;
        }

        .left-white {
            border-left: 1px solid white !important;
        }

        .center {
            text-align: center;
        }

        @media print {
            #btn-keterangan {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <table class="table">
        <tr>
            <td rowspan="3" class="header" style="width:150px;">
                <img src="{{ asset('images/logo.png') }}" alt="" width="70">
            </td>
            <td class="header">PT SINAR CEMARAMAS ABADI</td>
        </tr>
        <tr>
            <td class="header">LAPORAN CHECKLIST PEKERJAAN</td>
        </tr>
        <tr>
            <td class="header">Bulan : {{ $month }} {{ $year }} </td>

        </tr>
    </table>
    <table class="no-border-horizontal">
        <tr>
            <td></td>
        </tr>
    </table>
    <div style="font-weight:bold;margin-top:5px;margin-bottom:10px;">Lokasi : {{ $object->alamat_objek_kerja }} | Area :
        {{ $object->nama_objek_kerja }} | Grup : {{ $group->nama_grup_pengguna }}</div>
    <table class="table">
        <tr>
            <td style="width:30px;" class="header" rowspan="2">No</td>
            <td style="width:200px;" class="header" rowspan="2">Kegiatan</td>
            <td class="header" colspan="{{ $count_date }}">Tanggal</td>
        </tr>
        <tr>
            @for ($i = 1; $i <= $count_date; $i++)
                <td class="header" style="width:15px;">{{ $i }}</td>
            @endfor
        </tr>
        @php
            $counter = 0;
        @endphp
        @foreach ($jobs as $key => $job)
            @php
                $counter++;
            @endphp
            <tr>
                <td class="center">{{ $counter }}</td>
                <td>{{ $job }}</td>
                @for ($i = 1; $i <= $count_date; $i++)
                    @if (isset($answers[$key . '-' . $i]) && $answers[$key . '-' . $i]['jawaban'] == '1')
                        <td class="center"
                            style="{{ $answers[$key . '-' . $i]['checker'] == '1' ? 'background-color:#cbcbcb' : '' }}">
                            <img src="{{ asset('images/check-icon.png') }}" alt="" style="width:15px;">
                        </td>
                    @else
                        <td></td>
                    @endif
                @endfor
            </tr>
        @endforeach
    </table>
    <br>
    <table>
        <tr>
            <td style="background-color: #cbcbcb;width:15px;"></td>
            <td>Sudah diperiksa</td>
        </tr>
    </table>
    <table style="width:100%;margin-top:20px;">
        <tr>
            <td></td>
            <td class="center"><b>Dicetak oleh,</b></td>
            <td rowspan="3" style="width:70%;vertical-align:top;">

            </td>
            <td class="center"><b>Mengetahui,</b></td>
        </tr>
        <tr>
            <td style="height:70px;width:30px;"></td>
            <td></td>

            <td></td>
        </tr>
        <tr>
            <td></td>
            <td style="border-bottom:1px solid black;vertical-align:bottom;text-align:center;">
                {{ session()->get('user')->nama_pengguna }}
            </td>
            <td style="border-bottom:1px solid black"></td>
        </tr>
    </table>
    <div id="target-keterangan" style="padding-left:5px;margin-top:20px;">

    </div>
    <button type="button" id="btn-keterangan">Tambah Keterangan</button>
    <div id="row-keterangan" style="display:none;">
        <textarea name="input_keterangan" cols="100" rows="10"></textarea>
        <button type="button" id="save-keterangan">Simpan Keterangan</button>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $('#btn-keterangan').click(function() {
        $('#row-keterangan').show()
        $(this).hide()
    })

    $('#save-keterangan').click(function() {
        $('#target-keterangan').html('<b>Keterangan</b> :<br>' + $('[name="input_keterangan"]').val().replace(
            /\r?\n/g,
            '<br />'))
        $('#row-keterangan').hide()
        $('#btn-keterangan').show()
    })
    // window.print()
    // window.addEventListener('afterprint', (e) => {
    //     window.close()
    // })
</script>

</html>
