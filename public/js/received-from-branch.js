let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
    fps: 10,
    qrbox: 250
});

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
        searchable: false,
        render: function (data, type, row, meta) {
            let btn = '';
            if (row.id_pindah_barang_detail == '') {
                btn +=
                    '<a href="javascript:void(0)" class="btn btn-danger btn-xs mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a>';
            }

            return btn;
        }
    },
    ]
});

$('#table-detail-item').DataTable({
    pading: false,
    data: notReceived,
    ordering: false,
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
    }]
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
    getCodePindahGudang(dataselect.id)
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

function getCodePindahGudang(idCabang) {
    $('#cover-spin').show()
    $.ajax({
        url: urlReceivedFromBranchCode,
        data: {
            cabang: idCabang
        },
        success: function (res) {
            $('[name="id_pindah_barang2"]').empty()
            $('[name="id_pindah_barang2"]').select2({
                data: [{
                    'id': "",
                    'text': 'Pilih Kode Pindah Cabang'
                }, ...res.data]
            }).on('select2:select', function (e) {
                let dataselect = e.params.data
                $('[name="id_pindah_barang2"]').val(dataselect.id)
                $('[name="transporter"]').val(dataselect.transporter)
                $('[name="nomor_polisi"]').val(dataselect.nomor_polisi)
                $('[name="keterangan_pindah_barang"]').val(dataselect.keterangan_pindah_barang)
                $('[name="nama_cabang_asal"]').val(dataselect.nama_cabang)
                $('[name="id_cabang2"]').val(dataselect.id_cabang)
            });

            $('#cover-spin').hide()
        },
        error: function (error) {
            console.log(error)
            $('#cover-spin').hide()
        }
    })
}

$('.add-entry').click(function () {
    detailSelect = []
    $('#modalEntry').find('input,select,textarea').each(function (i, v) {
        $(v).val('').trigger('change')
    })

    $('#modalEntry').find('.setData').each(function (i, v) {
        $(v).text('')
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

$('.save-entry').click(function () {
    let modal = $('#modalEntry')
    let valid = validatorModal($('#qr_code').text())
    if (!valid.status) {
        html5QrcodeScanner.render(onScanSuccess, onScanError);
        Swal.fire("Gagal proses data. ", valid.message, 'error')
        return false
    }

    modal.find('.setData').each(function (i, v) {
        let id = $(v).prop('id')
        if (id == 'qty') {
            detailSelect[id] = normalizeNumber($(v).text())
        } else {
            detailSelect[id] = $(v).text()
        }
    })

    let newObj = Object.assign({}, detailSelect)
    if (statusModal == 'create') {
        details.push(newObj)
    } else if (statusModal == 'edit') {
        details[newObj.index - 1] = newObj
    }

    $('[name="details"]').val(JSON.stringify(details))

    statusModal = ''
    detailSelect = []

    resDataTable.clear().rows.add(details).draw()
    $('#modalEntry').modal('hide')
})

$('body').on('click', '.delete-entry', function () {
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
            details.splice(index, 1)
            resDataTable.clear().rows.add(details).draw()
            $('[name="details"]').val(JSON.stringify(details))
        }
    })
})

// function getDetailItem(id_pindah_barang) {
//     $('#cover-spin').show()
//     $.ajax({
//         url: '{{ route('received_from_branch-detail-item') }}',
//         data: {
//             id: id_pindah_barang
//         },
//         success: function(res) {
//             details = []
//             oldDetails = res.data
//             for (let i = 0; i < oldDetails.length; i++) {
//                 details.push(oldDetails[i])
//                 if (arrayQRCode.includes(oldDetails[i]['qr_code'])) {
//                     details[i]['status_diterima'] = 1
//                 } else {
//                     details[i]['id_pindah_barang_detail'] = 0
//                     details[i]['status_diterima'] = 0
//                 }
//             }

//             $('[name="details"]').val(JSON.stringify(details))
//             resDataTable.clear().rows.add(details).draw()
//             $('#cover-spin').hide()
//         },
//         error: function(error) {
//             console.log(error)
//             $('#cover-spin').hide()
//         }
//     })
// }

// $('body').on('change', '[name="checked_data"]', function() {
//     let index = $(this).parents('tr').index()
//     let detailSelect = details[index]
//     if ($(this).is(':checked')) {
//         detailSelect['status_diterima'] = 1
//     } else {
//         detailSelect['status_diterima'] = 0
//     }

//     details[index] = detailSelect
//     $('[name="details"]').val(JSON.stringify(details))
// })

$('.cancel-entry').click(function () {
    $('#modalEntry').modal('hide')
    html5QrcodeScanner.clear();
})

$("#modalEntry").on("hidden.bs.modal", function () {
    html5QrcodeScanner.clear();
});

function validatorModal(id = 0) {
    let message = 'Lengkapi inputan yang diperlukan'
    let valid = true
    let findItem = details.filter(p => p.qr_code == id)
    if (findItem.length > 0 && findItem[0].qr_code == id && statusModal == 'create') {
        message = "Barang sudah ada"
        valid = false
    }

    return {
        'status': valid,
        'message': message
    }
}

$("[name='search-qrcode']").on('keyup', function (e) {
    if ($("[name='search-qrcode']").val().trim().length == 10) {
        $('.btn-search').click()
    }
});

$('.btn-search').click(function () {
    let self = $('[name="search-qrcode"]').val()
    html5QrcodeScanner.clear();
    searchAsset(self)
})

$('.check-entry').click(function () {
    $('#modalNotReceived').modal();
})

function searchAsset(string) {
    $('#cover-spin').show()
    $.ajax({
        url: urlReceivedFromBranchDetailItem,
        type: 'get',
        data: {
            id_pindah_barang: $('[name="id_pindah_barang2"]').val(),
            qrcode: string
        },
        success: function (res) {
            for (select in res.data) {
                if (select == 'qty') {
                    $('#' + select).text(formatNumber(res.data[select]))
                } else {
                    $('#' + select).text(res.data[select])
                }

                $('#status_diterima').text(1)
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
    // audiobarcode.play();
    $('[name="search-qrcode"]').val(decodedText)
    $('.btn-search').click()
}

function onScanError(errorMessage) {
    toastr.error(JSON.strignify(errorMessage))
}