$(document).on('input', '.handle-number-4', function () {
    let str = $(this).val()
    if ((this).hasAttribute('data-max')) {
        $(this).val(formatRupiah(str, 4, $(this)))
    } else {
        $(this).val(formatRupiah(str, 4))
    }
})

$(document).on('input', '.handle-number-2', function () {
    let str = $(this).val()
    if ((this).hasAttribute('data-max')) {
        $(this).val(formatRupiah(str, 2, $(this)))
    } else {
        $(this).val(formatRupiah(str, 2))
    }
})

$('.handle-number-4').each(function (i, v) {
    let val = $(v).val().replace('.', ',')
    $(v).val(formatRupiah(val, 4))
})

$('.handle-number-2').each(function (i, v) {
    let val = $(v).val().replace('.', ',')
    $(v).val(formatRupiah(val, 2))
})

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

function formatNumber(angka) {
    angka = angka.toString().replace('.', ',')
    return formatRupiah(angka, 4)
}

function formatRupiah(angka, prefix, self = '') {
    angka = angka.toString().replace(/^\,/, '').replace(/[^,\d]/g, '')
    let number_string = angka.toString()

    if (self && normalizeNumber(number_string) > self.data('max')) {
        number_string = self.data('max').toString()
    }

    let split = number_string.split(','),
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
        number = number.replaceAll('.', '').replaceAll(',', '.')
    } else {
        number = number.replaceAll('.', '')
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
            deleteData(self.prop('href'))
        }
    })
})

if ($('.post-action').length > 0) {
    var validation = {
        submit: {
            settings: {
                form: 'form',
                inputContainer: '.form-group',
                errorListClass: 'form-control-error',
                errorClass: 'has-error',
                allErrors: true,
                scrollToError: {
                    offset: -100,
                    duration: 500
                }
            },
            callback: {
                onSubmit: function (node, formData) {
                    saveData()
                }
            }
        },
        dynamic: {
            settings: {
                trigger: 'keyup',
                delay: 1000
            },
        }
    }

    $.validate(validation)
}

function saveData() {
    let url = $('form').prop('action')
    $('#cover-spin').show()
    $.ajax({
        url: url,
        type: "POST",
        data: $("form").serialize(),
        dataType: "JSON",
        success: function (data) {
            $('#cover-spin').hide()
            if (data.result) {
                Swal.fire('Tersimpan!', data.message, 'success').then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = data.redirect;
                    }
                })
            } else {
                Swal.fire("Gagal Menyimpan Data. ", data.message, 'error')
            }
        },
        error: function (data) {
            $('#cover-spin').hide()
            Swal.fire("Gagal Menyimpan Data. ", data.responseJSON.message, 'error')
        }
    })
}

function deleteData(url) {
    $('#cover-spin').show()
    $.ajax({
        url: url,
        type: "get",
        dataType: "JSON",
        success: function (data) {
            $('#cover-spin').hide()
            if (data.result) {
                Swal.fire('Berhasil!', data.message, 'success').then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = data.redirect;
                    }
                })
            } else {
                Swal.fire("Gagal", data.message, 'error')
            }
        },
        error: function (data) {
            $('#cover-spin').hide()
            Swal.fire("Gagal", data.responseJSON.message, 'error')
        }
    })
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

console.log(getCookie("isi_token2_pengguna"))