<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
</head>
<body>
    <table>
        <tr>
            <th colspan="7" style="text-align: center;font-size: 16;"><b>Export Excel Master Akun</b></th>
        </tr>
        <tr></tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Kode Akun</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Nama Akun</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Tipe Akun</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Parent</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Header 1</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Header 2</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Header 3</th>
        </tr>
        @foreach ($akuns as $akun)
            <tr>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $akun->kode_akun }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $akun->nama_akun }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">
                    @php
                        switch ($akun->tipe_akun) {
                            case '0':
                                $tipe = 'Neraca';
                                break;
                            case '1':
                                $tipe = 'Laba Rugi';
                                break;
                            default:
                                $tipe = 'Undefined';
                                break;
                        }
                        echo $tipe;
                    @endphp
                </td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $akun->parent_name }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $akun->header1 }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $akun->header2 }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $akun->header3 }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>