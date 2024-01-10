let rmDetails = []
let detailSelect = []
let count = details.length
let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
    fps: 10,
    qrbox: 250
});
let maxZakRemaining = 0
let maxRemaining = 0

$('.datepicker').datepicker({
    format: 'yyyy-mm-dd',
});

$('.select2').select2()
var resDataTable = $('#table-detail').DataTable({
    scrollX: true,
    paging: false,
    data: details,
    ordering: false,
    columns: [{
        data: 'kode_batang',
        name: 'kode_batang',
        width: '100px'
    }, {
        data: 'nama_barang',
        name: 'nama_barang',
        width: '250px'
    }, {
        data: 'nama_satuan_barang',
        name: 'nama_satuan_barang',
        width: '80px'
    }, {
        data: 'jumlah',
        name: 'jumlah',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right',
        width: '100px'
    }, {
        data: 'jumlah_zak',
        name: 'jumlah_zak',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right',
        width: '100px'
    }, {
        data: 'tare',
        name: 'tare',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right',
        width: '100px'
    }, {
        data: 'nett',
        name: 'nett',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right',
        width: '100px'
    }, {
        data: 'catatan',
        name: 'catatan',
    }, {
        data: 'index',
        className: 'text-center',
        name: 'index',
        searchable: false,
        width: '50px',
        render: function (data, type, row, meta) {
            let btn = '<a href="' + urlDeleteDetail.replace('/0', '/' + data) + '" data-index="' + data +
                '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a>';

            return btn;
        }
    },],
    initComplete: function (settings, json) {
        sumDetail()
    },
    drawCallback: function (settings) {
        sumDetail()
    }
});

function sumDetail() {
    let totalJumlah = 0;
    let totalJumlahZak = 0;
    let totalTare = 0;
    let totalNett = 0;
    for (let i = 0; i < details.length; i++) {
        totalJumlah += parseFloat(details[i].jumlah)
        totalJumlahZak += parseFloat(details[i].jumlah_zak)
        totalTare += parseFloat(details[i].tare)
        totalNett += parseFloat(details[i].nett)
    }

    $('#table-detail').find('tfoot').remove()
    $('#table-detail tbody').after(
        '<tfoot><tr>' +
        '<td colspan="3" class="text-left"><b>Total</b></td>' +
        '<td class="text-right">' + formatNumberNew(totalJumlah, 4) + '</td>' +
        '<td class="text-right">' + formatNumberNew(totalJumlahZak, 4) + '</td>' +
        '<td class="text-right">' + formatNumberNew(totalTare, 4) + '</td>' +
        '<td class="text-right">' + formatNumberNew(totalNett, 4) + '</td>' +
        '<td></td><td></td>' +
        '</tr></tfoot>'
    );
}

$('[name="id_cabang"]').select2({
    data: [{
        'id': '',
        'text': 'Pilih Cabang'
    }, ...branch]
}).on('select2:select', function (e) {
    let dataselect = e.params.data
    getGudang(dataselect)
});

function getGudang(data) {
    $('[name="id_gudang"]').empty()
    $('[name="id_gudang"]').select2({
        data: [{
            'id': "",
            'text': 'Pilih Gudang'
        }, ...data.gudang]
    })
}

$('[name="id_timbangan"]').select2({
    data: timbangan
}).on('select2:select', function (e) {
    let dataselect = e.params.data
    $('.reload-timbangan').click()
})

$('.reload-timbangan').click(function () {
    reloadTimbangan()
})

function reloadTimbangan() {
    $.ajax({
        url: urlMaterialUsageReloadWeight,
        data: {
            id: $('[name="id_timbangan"]').val()
        },
        success: function (res) {
            if (parseFloat(res.data) > parseFloat(detailSelect.sisa_master_qr_code)) {
                $('[name="jumlah"]').addClass('error-field')
                $('#alertWeight').text('Berat melebihi stok').show()
            } else {
                $('[name="jumlah"]').removeClass('error-field')
                $('#alertWeight').text('').hide()
            }

            $('#modalEntry').find('[name="jumlah"]').val(formatNumber(res.data, 4))
        },
        error: function (error) {
            console.log(error)
        }
    })
}

$('.add-entry').click(function () {
    validateModalForm.resetForm();
    detailSelect = []
    $('#modalEntry').find('input,select,textarea').each(function (i, v) {
        $(v).val('').trigger('change')
        $(v).removeClass('error-field')
    })

    $('#alertWeight').text('').hide()
    $('#max-jumlah').text('')
    $('#max-jumlah-zak').text('')
    $('[name="checked_all"]').prop('checked', false)
    $('#modalEntry').modal()

    $('#message-stok').text('').hide()
    $('#alertZak').text('').hide()
    $('#label-timbangan').html('Timbangan')
    $('#label-berat').html('Berat Barang')
    $('#label-jumlah-zak').html('Jumlah Zak')
    $('[name="jumlah_zak"]').val(0)
    $('[name="weight_zak"]').val(0)
    $('.result-form').hide()

    setTimeout(() => {
        $('[name="search-qrcode"]').focus()
    }, 200);

    html5QrcodeScanner.render(onScanSuccess, onScanError);
})

$('#modalEntry').on('input', '[name="jumlah_zak"]', function () {
    let weightWrapper = $('[name="wrapper_weight"]').val()
    let jumlahZak = normalizeNumber($(this).val() ? $(this).val() : '0')
    let weightZak = jumlahZak * weightWrapper

    $('[name="jumlah_zak"]').val(jumlahZak)
    $('[name="weight_zak"]').val(weightZak.toFixed(4))
})

$('.cancel-entry').click(function () {
    html5QrcodeScanner.clear();
    $('#modalEntry').modal('hide')
})

$('body').on('click', '.delete-entry', function (e) {
    e.preventDefault()
    let url = $(this).prop('href')
    let index = $(this).parents('tr').index()
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
            deleteDetail(url)
        }
    })
})

function deleteDetail(url) {
    $('#cover-spin').show()
    $.ajax({
        url: url,
        type: 'post',
        success: function (data) {
            $('#cover-spin').hide()
            if (data.result) {
                Swal.fire('Berhasil!', data.message, 'success').then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = data.redirect;
                    }
                })
            } else {
                Swal.fire("Gagal proses Data. ", data.message, 'error')
            }
        },
        error: function (data) {
            $('#cover-spin').hide()
            Swal.fire("Gagal proses data. ", data.responseJSON.message, 'error')
        }
    })
}

$("[name='search-qrcode']").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        $('.btn-search').click()
    }
});

$('.btn-search').click(function () {
    let self = $('[name="search-qrcode"]').val().trim()
    html5QrcodeScanner.clear();
    searchAsset(self)
})

$('[name="is_qc"]').click(function () {
    details = []
    resDataTable.clear().rows.add(details).draw()
})

function searchAsset(string) {
    $('#cover-spin').show()
    let isQc = 0
    if ($('[name="is_qc"]').is(':checked')) {
        isQc = $('[name="is_qc"]').val()
    }

    $.ajax({
        url: urlMaterialUsageQrcode,
        type: 'get',
        data: {
            qrcode: string,
            id_cabang: $('[name="id_cabang"]').val(),
            id_gudang: $('[name="id_gudang"]').val(),
            is_qc: isQc,
        },
        success: function (res) {
            detailSelect = res.data
            let modal = $('#modalEntry')
            for (let key in detailSelect) {
                modal.find('[name="' + key + '"]').val(detailSelect[key])
                if (key == 'jumlah_zak' || key == 'weight_zak') {
                    modal.find('[name="' + key + '"]').val(0)
                }

                if (key == 'weight_zak') {
                    let weight = detailSelect.weight_zak / detailSelect.jumlah_zak
                    modal.find('[name="wrapper_weight"]').val(weight.toFixed(4))
                }
            }
            let sisaMasterQrCode = detailSelect.sisa_master_qr_code.toFixed(4);
            $('#max-jumlah').text('Sisa ' + formatNumberNew(sisaMasterQrCode, 4))
            $('[name="jumlah"]').val(0)

            maxRemaining = sisaMasterQrCode
            modal.find('[name="max_weight"]').val(sisaMasterQrCode)
            if (detailSelect.jumlah_zak == null) {
                // $('[name="jumlah_zak"]').rules("remove", "required");
                modal.find('[name="jumlah_zak"]').prop('readonly', true).removeClass('validate')
            } else {
                $('#label-jumlah-zak').html('Jumlah Zak <span>*</span>')
                maxZakRemaining = detailSelect.jumlah_zak
                $('#max-jumlah-zak').text('Sisa ' + formatNumber(detailSelect.jumlah_zak, 4))
                // $('[name="jumlah_zak"]').rules("add", "required");
                modal.find('[name="jumlah_zak"]').prop('readonly', false).addClass('validate')
            }

            $('#label-berat').html('Berat Barang <span>*</span>')

            if (detailSelect.isweighed == 1) {
                // $('[name="jumlah"]').rules("add", "required");
                modal.find('[name="jumlah"]').prop('readonly', true).addClass('validate')
                $('[name="id_timbangan"]').rules("add", "required");
                modal.find('[name="id_timbangan"]').prop('disabled', false).addClass('validate')
                $('#label-timbangan').html('Timbangan <span>*</span>')
                $('.reload-timbangan').show()
            } else {
                // $('[name="jumlah"]').rules("add", "required");
                modal.find('[name="jumlah"]').prop('readonly', false).addClass('validate')
                $('[name="id_timbangan"]').rules("remove", "required");
                modal.find('[name="id_timbangan"]').prop('disabled', true).removeClass('validate')
                $('.reload-timbangan').hide()
            }

            if ($('[name="jenis_pemakaian"]').val() == 'Kerugian Lain') {
                $('[name="checked_all"]').prop('disabled', false)
            } else {
                $('[name="checked_all"]').prop('disabled', true)
            }

            $('.result-form').show()
            $('#cover-spin').hide()
        },
        error: function (error) {
            let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                .statusText
            Swal.fire("Gagal Mengambil Data. ", textError, 'error')
            html5QrcodeScanner.render(onScanSuccess, onScanError);
            $('[name="search-qrcode"]').val('')
            $('#cover-spin').hide()
            $('.result-form').hide()
        }
    })
}

function onScanSuccess(decodedText, decodedResult) {
    $('[name="search-qrcode"]').val(decodedText)
    $('.btn-search').click()
}

function onScanError(errorMessage) {
    toastr.error(JSON.strignify(errorMessage))
}

$('[name="checked_all"]').change(function () {
    let modal = $('#modalEntry')
    if ($(this).is(':checked')) {
        if ($('[name="jenis_pemakaian"]').val() == 'Kerugian Lain') {
            modal.find('[name="id_timbangan"]').val('').prop('disabled', true).removeClass('validate')
                .change()
            $('.reload-timbangan').hide()
            modal.find('[name="jumlah"]').val(formatNumber(detailSelect.sisa_master_qr_code, 4)).prop(
                'readonly', true).addClass('validate')
            modal.find('[name="jumlah_zak"]').val(formatNumber(detailSelect.jumlah_zak, 4)).prop('readonly',
                true)
            let weight = modal.find('[name="wrapper_weight"]').val()
            modal.find('[name="weight_zak"]').val(weight * detailSelect.jumlah_zak)
        }
    } else {
        modal.find('[name="jumlah_zak"]').val(0).prop('readonly', false)
        modal.find('[name="weight_zak"]').val(0)
        if (detailSelect.isweighed == 1) {
            modal.find('[name="jumlah"]').prop('readonly', true).addClass('validate').val(0)
            modal.find('[name="id_timbangan"]').prop('disabled', false).addClass('validate')
            $('#label-timbangan').html('Timbangan <span>*</span>')
            $('.reload-timbangan').show()
        } else {
            modal.find('[name="jumlah"]').prop('readonly', false).addClass('validate').val(0)
            modal.find('[name="id_timbangan"]').prop('disabled', true).removeClass('validate')
            $('.reload-timbangan').hide()
        }
    }
})

let validateForm = $(".post-acton-custom").validate({
    rules: {
        id_cabang: "required",
        id_gudang: "required",
        tanggal: 'required',
        jenis_pemakaian: 'required'
    },
    errorClass: 'has-error',
    highlight: function (element, errorClass, validClass) {
        $(element).parents("div.form-group").addClass('error');
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).parents(".error").removeClass('error');
    },
    submitHandler: function (form, e) {
        e.preventDefault()
        saveData($(form))
        return false;
    }
});

let validateModalForm = $('.post-action-modal').validate({
    rules: {
        jumlah_zak: {
            required: true,
            maxWithZero: function () {
                return formatNumberNew(maxZakRemaining, 4);
            }
        },
        jumlah: {
            required: true,
            maxNotZero: function () {
                return formatNumberNew(maxRemaining, 4);
            }
        }
    },
    errorClass: 'has-error',
    highlight: function (element, errorClass, validClass) {
        $(element).parents("div.form-group").addClass('error');
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).parents(".error").removeClass('error');
    },
    submitHandler: function (form, e) {
        e.preventDefault()
        saveData($(form))
        return false;
    }
});

$.extend($.validator.messages, {
    required: "Tidak boleh kosong",
    email: "Pastikan format email sudah benar",
    number: "Pastikan hanya angka",
});

$.validator.addMethod(
    'maxWithZero',
    function (value, element, param) {
        let val = normalizeNumber(value)
        param = normalizeNumber(param)
        return this.optional(element) || (val <= param)
    },
    'Tidak boleh lebih dari {0}'
);

$.validator.addMethod(
    'maxNotZero',
    function (value, element, param) {
        let val = normalizeNumber(value)
        param = normalizeNumber(param)
        return this.optional(element) || ((val > 0) && (val <= param))
    },
    'Tidak boleh 0 atau lebih dari {0}'
);