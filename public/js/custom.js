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

function formatNumber(angka, lengthComa) {
    angka = angka.toString().replace('.', ',')
    return formatRupiah(angka, lengthComa)
}

function formatNumberNew(number, prefix = 0) {
    if (typeof number === 'string') {
        if (number.includes(',')) {
            let reg = number.match(/^([^,]*,[^,]*)/);
            number = normalizeNumber(reg[0])
        }
    }

    if (number == '0') {
        return 0;
    } else {
        return new Intl.NumberFormat("id-ID", {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: prefix
        }).format(number);
    }
}

function formatRupiah(angka, prefix, self = '') {
    let labelMinus = ''
    if (angka[0] == '-') {
        labelMinus = '-'
    }

    angka = angka.toString().replace(/^\,|^0/, '0').replace(/[^,\d]/g, '')
    let number_string = angka.toString()

    if (self && normalizeNumber(number_string) > self.data('max')) {
        number_string = self.data('max').toString()
    }

    let split = number_string.split(',');
    split[0] = '' + parseInt(split[0])
    let sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? labelMinus + rupiah + ',' + (split[1].length > prefix ? split[1].substring(0, prefix) :
        split[1]) : labelMinus + rupiah;
    return rupiah;
}

function normalizeNumber(number) {
    number = number ? number : '0'
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
        title: 'Anda yakin ingin membatalkan data ini?',
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
                    saveData(node)
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

function saveData(node) {
    let url = node.prop('action')
    $('#cover-spin').show()
    node.find('.handle-number-4,.handle-number-2').each(function (i, v) {
        $(v).val(normalizeNumber($(v).val()))
    })

    $.ajax({
        url: url,
        type: "POST",
        data: node.serialize(),
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
                Swal.fire("Gagal Proses Data.", data.message, 'error')
            }
        },
        error: function (data) {
            $('#cover-spin').hide()
            Swal.fire("Gagal Proses Data.", data.responseJSON.message, 'error')
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

var _cf = (function () {
    function _shift(x) {
        var parts = x.toString().split('.');
        return (parts.length < 2) ? 1 : Math.pow(10, parts[1].length);
    }
    return function () {
        return Array.prototype.reduce.call(arguments, function (prev, next) { return prev === undefined || next === undefined ? undefined : Math.max(prev, _shift(next)); }, -Infinity);
    };
})();

Math.tambah = function () {
    var f = _cf.apply(null, arguments); if (f === undefined) return undefined;
    function cb(x, y, i, o) { return x + f * y; }
    return customRound(Array.prototype.reduce.call(arguments, cb, 0) / f, 4);
};

Math.kurang = function (l, r) { var f = _cf(l, r); return customRound((l * f - r * f) / f, 4); };

Math.kali = function () {
    var f = _cf.apply(null, arguments);
    function cb(x, y, i, o) { return (x * f) * (y * f) / (f * f); }
    return customRound(Array.prototype.reduce.call(arguments, cb, 1), 4);
};

Math.bagi = function (l, r) { var f = _cf(l, r); return customRound((l * f) / (r * f), 4); };

function customRound(number, prefix = 0) {
    let splitNumber = number.toString().split('.')
    let res = splitNumber.length == 2 ? number.toFixed(prefix) : number;
    return res;
}

// $('#tabel').on('search.dt', function () {
//     var value = $('.dataTables_filter input').val();
//     sessionStorage.setItem("search_" + pageName, value);
// });

// $('#tabel').on('length.dt', function (e, settings, len) {
//     // sessionStorage.setItem("length_" + pageName, len);
// });

// if (sessionStorage.getItem("list_" + pageName) === null) {
//     variableGlobal['length_' + pageName] = 50;
// } else {
//     variableGlobal['length_' + pageName] = parseInt(sessionStorage.getItem("length_" + pageName));
// }

// if (sessionStorage.getItem("page_" + pageName) === null) {
//     variableGlobal["page_" + pageName] = 0
// } else {
//     variableGlobal["page_" + pageName] = parseInt(sessionStorage.getItem("page_" + pageName))
// }

// if (sessionStorage.getItem("display_" + pageName) === null) {
//     variableGlobal["display_" + pageName] = 0
// } else {
//     variableGlobal["display_" + pageName] = variableGlobal['length_' + pageName]
// }

// if (sessionStorage.getItem("search_" + pageName) === null) {
//     variableGlobal["search_" + pageName] = ''
// } else {
//     variableGlobal["search_" + pageName] = sessionStorage.getItem("search_" + pageName);
// }