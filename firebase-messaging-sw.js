importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyC52DsEyBnO7txToVXyjXWwtrygQDW1zGc",
    authDomain: "homegogomarket.firebaseapp.com",
    projectId: "homegogomarket",
    storageBucket: "homegogomarket.firebasestorage.app",
    messagingSenderId: "1000922177401",
    appId: "1:1000922177401:web:167b5cddc73aac616559a8",
    measurementId: "G-GW5BR51G2W"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});