$(document).on('input', '.handle-number-4', function () {
    let str = $(this).val()
    $(this).val(formatRupiah(str, 4))
})

$('.handle-number-4').each(function (i, v) {
    let val = $(v).val().replace('.', ',')
    $(v).val(formatRupiah(val, 4))
})

function formatNumber(angka) {
    angka = angka.toString().replace('.', ',')
    return formatRupiah(angka, 4)
}

function formatRupiah(angka, prefix) {

    angka = angka.toString().replace(/^\,/, '')
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + (split[1].length > prefix ? split[1].substring(0, prefix) :
        split[1]) : rupiah;
    return rupiah;
}

function normalizeNumber(number) {
    if (number.indexOf(',')) {
        number = number.replace('.', '').replace(',', '.')
    }

    return parseFloat(number)
}