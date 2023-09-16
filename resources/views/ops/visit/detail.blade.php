<table>
    <tr>
        <td style="width: 175px">Issue</td>
        <td>{{ $data->visit_title != '' ? $data->visit_title : '-' }}</td>
    </tr>
    <tr>
        <td>Deskripsi</td>
        <td>{{ $data->visit_desc != '' ? $data->visit_desc : '-' }}
        </td>
    </tr>
    @if ($data->status == '0')
        <tr>
            <td>Alasan Pembatalan</td>
            <td>{{ $data->alasan_pembatalan }}</td>
        </tr>
    @endif
    @if ($data->visit_type)
        <tr>
            <td>Tipe Visit</td>
            <td>
                {{ $data->visit_type }}
            </td>
        </tr>
    @endif
    @if ($data->progress_ind)
        <tr>
            <td>Progress Indicator</td>
            <td>
                {{ App\Enums\PotensialEnum::getLabel($data->progress_ind) }}
            </td>
        </tr>
        @if ($data->progress_ind == 2)
            <tr>
                <td>Range Potensial</td>
                <td>
                    <div class="progressa">
                        <div class="progressab progressab-{{ $data->id }}"
                            style="    background-color: rgb(178, 222, 75);"></div>
                    </div>
                </td>
            </tr>
        @endif

        @if ($data->progress_ind == 3)
            <tr>
                <td>Nomor Sales Order</td>
                <td>
                    {{ $data->sales_order ? $data->sales_order->nama_permintaan_penjualan : '-' }}
                </td>
            </tr>
        @endif
    @endif

    @if ($data->status == '2')
        <tr>
            <td>Lokasi Visit</td>
            <td>
                <a href="javascript:;"
                    onclick="window.open('https://maps.google.com/maps?q={{ $data->latitude_visit }},{{ $data->longitude_visit }}&hl=id&z=14&amp;')">Klik
                    untuk melihat lokasi</a>
            </td>
        </tr>
    @endif
    @if ($data->progress_ind)
        <tr>
            <td>Bukti Visit</td>
            <td>
                <a href="javascript:;"
                    onclick="openModalBukti('{{ $data->visit_code }}','{{ $data->proofment_1 }}','{{ $data->proofment_2 }}')">Klik
                    untuk melihat gambar</a>
            </td>
        </tr>
    @endif

</table>
<script>
    @if ($data->progress_ind == 2)
        var progressval = {{ $data->range_potensial ?? 0 }}
        var elm = document.getElementsByClassName('progressab-{{ $data->id }}')[0];
        elm.style.width = progressval + "%";

        elm.innerText = "Potensial " + progressval;

        if (progressval > 90 && progressval <= 100) {
            elm.style.backgroundColor = 'blue';
        } else if (progressval > 50 && progressval < 90) {
            elm.style.backgroundColor = 'green';
        } else if (progressval <= 50) {
            elm.style.backgroundColor = 'red';
        }
    @endif
</script>
