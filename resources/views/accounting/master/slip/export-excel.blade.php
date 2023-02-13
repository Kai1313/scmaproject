{{-- <table>
    <tr>
        <th colspan="4" style="text-align: center;font-size: 16;"><b>Export Excel Master Slip</b></th>
    </tr>
    <tr></tr>
    <tr>
        <th>Kode Slip</th>
        <th>Nama Slip</th>
        <th>Jenis Slip</th>
        <th>Akun</th>
    </tr>
    @foreach ($slips as $slip)
        <tr>
            <td>{{ $slip->kode_slip }}</td>
            <td>{{ $slip->nama_slip }}</td>
            <td>
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
            <td>{{ $slip->nama_akun }}</td>
        </tr>
    @endforeach
</table> --}}

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
            <th colspan="4" style="text-align: center;font-size: 16;"><b>Export Excel Master Slip</b></th>
        </tr>
        <tr></tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Kode Slip</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Nama Slip</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Jenis Slip</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Akun</th>
        </tr>
        @foreach ($slips as $slip)
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
        @endforeach
    </table>
</body>
</html>