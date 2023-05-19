importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyD1F8fbvIJJ2f5tP1Bnh1zeZt6LJZP-qwg",
    authDomain: "sisca-65dc2.firebaseapp.com",
    projectId: "sisca-65dc2",
    storageBucket: "sisca-65dc2.appspot.com",
    messagingSenderId: "720882934197",
    appId: "1:720882934197:web:5011bb93fa44d36be579fc",
    measurementId: "G-GPYK11T03Y"
});

const messaging = firebase.messaging();