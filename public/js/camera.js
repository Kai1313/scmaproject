var cameraOptions = document.getElementById('optionCamera');
var video = document.querySelector('video');
var canvas = document.querySelector('canvas');
var stackStream

$('.show-modal-camera').click(function () {
    $('#modalEntryCamera').modal('show')
    startCamera()

})

async function startCamera() {
    let deviceId = localStorage.getItem('deviceId')
    let updatedConstraints
    if (!deviceId) {
        updatedConstraints = {
            video: {
                width: 1280,
                height: 720,
            },
        };
    } else {
        updatedConstraints = {
            video: {
                width: 1280,
                height: 720,
                deviceId: {
                    exact: deviceId
                }
            },
        };
    }

    if ('mediaDevices' in navigator && navigator.mediaDevices.getUserMedia) {
        startStream(updatedConstraints);
    }
}

async function startStream(constraints) {
    const stream = await navigator.mediaDevices.getUserMedia(constraints);
    if (stream) {
        handleStream(stream);
    }
};

function handleStream(stream) {
    video.srcObject = stream;
    let videoTrack = stream.getVideoTracks()[0];
    stackStream = videoTrack
    const capabilities = videoTrack.getCapabilities()
    if (!capabilities.zoom) {
        return;
    }
};

async function getCameraSelection() {
    let deviceId = localStorage.getItem('deviceId')
    let devices = await navigator.mediaDevices.enumerateDevices();
    let videoDevices = devices.filter(device => device.kind === 'videoinput');
    let options = videoDevices.map(videoDevice => {
        if (videoDevice.deviceId == deviceId) {
            return `<option value="${videoDevice.deviceId}" selected>${videoDevice.label}</option>`;
        } else {
            return `<option value="${videoDevice.deviceId}">${videoDevice.label}</option>`;
        }
    });
    options.push(`<option value="">Pilih Kamera</option>`)
    cameraOptions.innerHTML = options.join('');
};

getCameraSelection();

$('#optionCamera').change(function () {
    let self = $(this).val()
    changeCamera(self)
})

function changeCamera(self) {
    localStorage.setItem('deviceId', self)
    const updatedConstraints = {
        video: {
            width: 1920,
            height: 1080,
            deviceId: self
        },
    };
    startStream(updatedConstraints);
};

$('.hide-camera-item').click(function () {
    if (stackStream) {
        stackStream.stop()
    }

    $('#modalEntryCamera').modal('hide')
})

$('.snap').click(function () {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    var context = canvas.getContext("2d");
    context.drawImage(video, 0, 0);

    let url = canvas.toDataURL('image/jpeg');
    snapAjax(url)
})

function snapAjax(url) {
    $('#cover-spin').show()
    $.ajax({
        url: urlPhoto,
        type: 'post',
        data: {
            data_url: url,
            type: 'base64'
        },
        success: function (result) {
            console.log(result)
            let html = '<div style="display:inline-block;margin:5px;">'
                + '<div style="margin-bottom:10px;"><a href="' + url + '" data-fancybox="lightbox">'
                + '<img src="' + url + '" alt="" style="width:100px;height:100px;object-fit:cover;border-radius:5px;" loading="lazy"></a></div>'
                + '<a href="javascript:void(0)" class="remove-image" style="margin-right:20px;color:red;" data-id="' + result.id + '"><i class="fa fa-trash" style="font-size:20px"></i></a>'
                + '</div>'

            $('.show-res-camera').append(html)
            console.log($('.show-res-camera'))
            Fancybox.bind('[data-fancybox="lightbox"]');

            setTimeout(() => {
                $(".show-res-camera").scrollLeft($(".show-res-camera").get(0).scrollWidth);
            }, 500);
            $('#cover-spin').hide()
        }, error: function (error) {
            Swal.fire("Gagal Menyimpan Data. ", error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error.statusText, 'error')
            $('#cover-spin').hide()
        }
    })
}

$('body').on('click', '.remove-image', function () {
    let id = $(this).data('id')
    Swal.fire({
        title: 'Apakah Kamu Yakin?',
        text: "Kamu akan menghapus data ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Tidak',
        confirmButtonText: 'Ya'
    }).then((result) => {
        if (result.isConfirmed) {
            let self = $(this)
            $('#cover-spin').show()
            $.ajax({
                url: urlPhotoDelete,
                type: 'get',
                data: {
                    id_media: id
                },
                success: function (result) {
                    self.parent().remove()
                    $('#cover-spin').hide()
                },
                error: function (error) {
                    Swal.fire("Gagal Menyimpan Data. ", error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error.statusText, 'error')
                    $('#cover-spin').hide()
                }
            })
        }
    })
})

$('#modalEntryCamera').on('hidden.bs.modal', function (e) {
    if (stackStream) {
        stackStream.stop()
    }
    // $('#modalEntryCamera').modal('hide')
})

