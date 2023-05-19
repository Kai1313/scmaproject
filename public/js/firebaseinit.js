var firebaseConfig = {
    apiKey: "AIzaSyD1F8fbvIJJ2f5tP1Bnh1zeZt6LJZP-qwg",
    authDomain: "sisca-65dc2.firebaseapp.com",
    projectId: "sisca-65dc2",
    storageBucket: "sisca-65dc2.appspot.com",
    messagingSenderId: "720882934197",
    appId: "1:720882934197:web:5011bb93fa44d36be579fc",
    measurementId: "G-GPYK11T03Y"
};
var isi_token_pengguna = getCookie("isi_token_pengguna");

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.requestPermission().then(function () {
    return messaging.getToken()
}).then(function (token) {
    if (!localStorage.getItem('fcmToken')) {
        $.ajax({
            url: siteMain + '/api/store_fcm_token',
            type: 'post',
            data: { fcm_token: token, token: isi_token_pengguna },
            success: function (res) {
                localStorage.setItem('fcmToken', token)
                console.log(res)
            },
            error: function (error) {
                console.error(error)
            }
        })
    }
}).catch(function (err) {
    console.log("Unable to get permission to notify.")
});

var textVoice = ''

messaging.onMessage(function (payload) {
    let message = payload.data.body
    textVoice = message

    $('.play-voice').click()
    // var notify;
    // notify = new Notification(payload.notification.title, {
    //     body: payload.notification.body,
    //     icon: payload.notification.icon,
    // });

    // let html = '<li class="media"><div class="media-left"><span class="badge bg-danger-400 media-badge">-</span></div>'
    //     + '<div class="media-body">'
    //     + '<a href="javascript:void(0)" class="media-heading">'
    //     + '<span class="text-semibold">' + payload.notification.title + '</span>'
    //     + '<span class="media-annotation pull-right"></span>'
    //     + '</a><span class="text-muted">' + payload.notification.body + '</span></div></li>'

    // $('.dropdown-content-body').prepend(html)
    // let countNumber = parseFloat($('.badge-notif').text())
    // $('.badge-notif').text(countNumber + 1).css('display', 'block')

    // $('#btn-notification-effect').click()
    // Swal.fire({
    //     position: 'center',
    //     icon: 'info',
    //     title: payload.notification.title,
    //     text: payload.notification.body,
    //     showConfirmButton: false,
    //     timer: 5000
    // })

    // if (payload.notification.title.toLowerCase() == 'permintaan ambilan') {
    //     if ($('#menu-request_trans').find('.request_unread').length == 0) {
    //         // console.log($('#menu-request_trans').find('.request_unread').length)
    //         $('#menu-request_trans').append('<span class="label label-warning request_unread"><i class="icon-warning"></i></span>')
    //     }
    // }
});

// self.addEventListener('notificationclick', function (event) {
//     event.notification.close();
// });

// $('.btn-logout').click(function (e) {
//     messaging.deleteToken()
// })

// $('#btn-notification-effect').click(function () {
//     notifSound.play();
// })

$('.play-voice').click(function () {
    voice(textVoice)
})

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

window.speechSynthesis.cancel()

async function voice(text) {
    let msg = new SpeechSynthesisUtterance();
    msg.rate = 0.8;
    msg.pitch = 1.1;
    msg.lang = 'id-ID';
    msg.text = text;
    await window.speechSynthesis.speak(msg);
    textVoice = ''
}
