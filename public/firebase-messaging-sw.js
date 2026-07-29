importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyD_DmXQ1ciIbmvV77upMPP8l3flW0kTSqk",
  authDomain: "lattessa-7d7cc.firebaseapp.com",
  projectId: "lattessa-7d7cc",
  storageBucket: "lattessa-7d7cc.firebasestorage.app",
  messagingSenderId: "600523513554",
  appId: "1:600523513554:web:5e5261cb5450ae06f6170b"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  const { title, body } = payload.notification || {};
  self.registration.showNotification(title || 'Lattessa', {
    body: body || '',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-72x72.png',
    data: payload.data || {}
  });
});
