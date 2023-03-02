$(document).on('input', '.handle-number-4', function () {
    let str = $(this).val()
    $(this).val(formatRupiah(str, 4))
})

$('.handle-number-4').each(function (i, v) {
    let val = $(v).val().replace('.', ',')
    $(v).val(formatRupiah(val, 4))
})

$(function () {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus()
    })

    $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
        $(this).closest(".select2-container").siblings('select:enabled').select2('open')
    })

    $('select.select2').on('select2:closing', function (e) {
        $(e.target).data("select2").$selection.one('focus focusin', function (e) {
            e.stopPropagation();
        })
    })
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

$('body').on('click', '.btn-destroy', function (e) {
    let self = $(this)
    e.preventDefault();
    Swal.fire({
        title: 'Anda yakin ingin menghapus data ini?',
        icon: 'info',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No',
        reverseButtons: true,
        customClass: {
            actions: 'my-actions',
            confirmButton: 'order-1',
            denyButton: 'order-3',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = self.prop('href')
        }
    })
})