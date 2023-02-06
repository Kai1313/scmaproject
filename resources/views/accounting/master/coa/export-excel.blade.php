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
        {{-- @foreach ($slips as $slip)
            <tr>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $slip->kode_slip }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $slip->nama_slip }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">
                    @php
                        switch ($slip->jenis_slip) {
                            case '1':
                                $jenis = 'Bank';
                                break;
                            case '2':
                                $jenis = 'PG';
                                break;
                            case '3':
                                $jenis = 'HG';
                                break;
                            default:
                                $jenis = 'Kas';
                                break;
                        }
                        echo $jenis;
                    @endphp
                </td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $slip->nama_akun }}</td>
            </tr>
        @endforeach --}}
    </table>
</body>
</html>