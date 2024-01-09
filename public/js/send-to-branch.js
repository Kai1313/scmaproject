let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
    fps: 10,
    qrbox: 250
});
let deleteDetails = []
let detailSelect = []
let statusModal = 'create'

$('[name="details"]').val(JSON.stringify(details))

var resDataTable = $('#table-detail').DataTable({
    scrollX: true,
    paging: false,
    data: details,
    ordering: false,
    drawCallback: function () {
        var allData = this.api().column(0).data().toArray();
        var toFindDuplicates = allData => allData.filter((item, index) => allData.indexOf(item) !==
            index)
        var duplicateElementa = toFindDuplicates(allData);
        var indexs = []
        for (let i = 0; i < duplicateElementa.length; i++) {
            let indexDuplicate = allData.indexOf(duplicateElementa[i])
            $($('#table-detail tbody tr:eq(' + indexDuplicate + ')')).css('color', 'red')
        }
    },
    columns: [{
        data: 'qr_code',
        name: 'qr_code'
    }, {
        data: 'nama_barang',
        name: 'nama_barang'
    }, {
        data: 'qty',
        name: 'qty',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right'
    }, {
        data: 'nama_satuan_barang',
        name: 'nama_satuan_barang'
    }, {
        data: 'batch',
        name: 'batch',
        className: 'text-right'
    }, {
        data: 'tanggal_kadaluarsa',
        name: 'tanggal_kadaluarsa',
    }, {
        data: 'sg',
        name: 'sg',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right'
    }, {
        data: 'be',
        name: 'be',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right'
    }, {
        data: 'ph',
        name: 'ph',
        render: function (data) {
            return data ? formatNumber(data, 4) : 0
        },
        className: 'text-right'
    }, {
        data: 'bentuk',
        name: 'bentuk',
    },
    {
        data: 'warna',
        name: 'warna',
    }, {
        data: 'keterangan',
        name: 'keterangan',
    },
    {
        data: 'id_pindah_barang_detail',
        className: 'text-center',
        name: 'id_pindah_barang_detail',
        width: 80,
        searchable: false,
        render: function (data, type, row, meta) {
            let btn = '';
            if (row.id_pindah_barang_detail != '') {
                btn +=
                    '<a href="javascript:void(0)" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a>';
            }

            if (!qrcodeReceived.includes(row.qr_code)) {
                btn +=
                    '<a href="' + urlDeleteDetail.replace('/0', '/' + data) + '" data-index="' + data +
                    '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a>';
            }

            return btn;
        }
    },
    ]
});

$('.select2').select2()
$('.datepicker').datepicker({
    format: 'yyyy-mm-dd',
});

$('[name="id_cabang"]').select2({
    data: [{
        'id': '',
        'text': 'Pilih Cabang'
    }, ...branches]
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

    let branchData = []
    for (let i = 0; i < allBranch.length; i++) {
        if (allBranch[i].id != data.id) {
            branchData.push(allBranch[i])
        }
    }
    $('[name="id_cabang2"]').empty()
    $('[name="id_cabang2"]').select2({
        data: [{
            'id': '',
            'text': 'Pilih Cabang Tujuan'
        }, ...branchData]
    })
}

$('.add-entry').click(function () {
    detailSelect = []
    $('#modalEntry').find('input,select,textarea').each(function (i, v) {
        $(v).val('').trigger('change')
    })

    $('#modalEntry').find('.setData').each(function (i, v) {
        if ($(v).prop('id') == 'keterangan') {
            $(v).find('textarea').val('')
        } else {
            $(v).text('')
        }
    })

    statusModal = 'create'
    $('#modalEntry').modal({
        backdrop: 'static',
        keyboard: false
    })

    setTimeout(() => {
        $('[name="search-qrcode"]').focus()
    }, 200);

    html5QrcodeScanner.render(onScanSuccess, onScanError);
    $('.result-form').hide()
})

$('.cancel-entry').click(function () {
    $('#modalEntry').modal('hide')
    html5QrcodeScanner.clear();
})

$('body').on('click', '.delete-entry', function (e) {
    e.preventDefault()
    let url = $(this).prop('href')
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

function searchAsset(string) {
    $('#cover-spin').show()
    $.ajax({
        url: urlSearchQrcode,
        type: 'get',
        data: {
            id_cabang: $('[name="id_cabang"]').val(),
            id_gudang: $('[name="id_gudang"]').val(),
            qrcode: string,
            id: sendId
        },
        success: function (res) {
            for (select in res.data) {
                if (select == 'qty') {
                    $('#' + select).val(formatNumberNew(res.data[select], 4))
                } else {
                    if (select == 'keterangan') {
                        $('#' + select).find('textarea').val(res.data[select])
                    } else {
                        $('#' + select).val(res.data[select])
                    }
                }
            }

            $('[name="search-qrcode"]').val('')
            $('.result-form').show()
            $('#cover-spin').hide()
        },
        error: function (error) {
            let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                .statusText
            Swal.fire("Gagal Mengambil Data. ", textError, 'error')
            html5QrcodeScanner.render(onScanSuccess, onScanError);
            $('.result-form').hide()
            $('#cover-spin').hide()
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

$('body').on('click', '.edit-entry', function () {
    let index = $(this).parents('tr').index()
    let selData = details[index]
    let modal = $('#modalEntryEdit')
    $('#modalEntryEdit').find('input,textarea').each(function (i, v) {
        let nameInput = $(v).prop('name')
        if (nameInput == 'qty') {
            $(v).val(formatNumberNew(selData[nameInput], 4))
        } else {
            $(v).val(selData[nameInput])
        }

    })
    modal.modal('show')
})

let validateForm = $(".post-action-custom").validate({
    rules: {
        id_cabang: "required",
        id_gudang: "required",
        tanggal_pindah_barang: 'required',
        id_cabang2: "required"
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

let validateModalEditForm = $('.post-action-edit-modal').validate({
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