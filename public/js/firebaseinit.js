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
});

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
