let defaultUrlPrint = []
let param = ''
let gArray = [];
let table = ''
let reportType = 'Rekap'

$('.btn-action').each(function (i, v) {
    defaultUrlPrint.push($(v).prop('href'))
})

$('.select2').select2()

$('[name="date"]').daterangepicker({
    timePicker: false,
    startDate: moment().subtract(30, 'days'),
    endDate: moment(),
    locale: {
        format: 'YYYY-MM-DD'
    }
});

$('.btn-action').each(function (i, v) {
    $(v).prop('href', defaultUrlPrint[i] + param)
})

$('.btn-view-action').click(function () {
    if ($('[name="type"]').length > 0) {
        if (table && reportType == $('[name="type"]').val()) {
            table.ajax.url(param).load()
        } else {
            loadDatatable()
        }
    } else {
        if (table) {
            table.ajax.url(param).load()
        } else {
            loadDatatable()
        }
    }
})

$('[name="id_cabang"]').select2().on('select2:select', function (e) {
    let dataselect = e.params.data
    if ($('[name="id_gudang"]').length > 0) {
        clearWarehouse()
        for (let i = 0; i < branch.length; i++) {
            if (branch[i].id == dataselect.id) {
                getWarehouse(branch[i].gudang)
                break
            }
        }
    }
});

if ($('[name="id_gudang"]').length > 0) {
    clearWarehouse()
    if (branch.length == 1) {
        if (branch[0].gudang.length > 0) {
            getWarehouse(branch[0].gudang)
        }
    }

    getParam()
} else {
    getParam()
}

$('.trigger-change').change(function () {
    getParam()
})

function getParam() {
    param = ''
    $('.trigger-change').each(function (i, v) {
        param += (i == 0) ? '?' : '&'
        param += $(v).prop('name') + '=' + $(v).val()
    })

    $('.btn-action').each(function (i, v) {
        $(v).prop('href', defaultUrlPrint[i] + param)
    })
}

function getWarehouse(arrayGudang) {
    gArray = []
    if (arrayGudang.length > 0) {
        gArray.push({
            'id': arrayGudang.map(s => s.id).join(','),
            'text': 'Semua Gudang'
        })
    }

    for (let a = 0; a < arrayGudang.length; a++) {
        gArray.push({
            'id': arrayGudang[a].id,
            'text': arrayGudang[a].text
        })
    }

    $('[name="id_gudang"]').empty()
    $('[name="id_gudang"]').select2({
        data: gArray
    })
}

function clearWarehouse() {
    let tempId = []
    for (let i = 0; i < branch.length; i++) {
        for (let a = 0; a < branch[i].gudang.length; a++) {
            tempId.push(branch[i].gudang[a].id)
        }
    }

    $('[name="id_gudang"]').empty()
    $('[name="id_gudang"]').select2({
        data: [{
            'id': tempId.join(','),
            'text': 'Semua Gudang'
        }]
    })
}

function formatDate(date) {
    const d = new Date(date);
    return moment(d).format('DD MMM YYYY');
}
