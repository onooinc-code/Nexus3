<?php

return [
    'driver' => env('NOTIFICATION_DRIVER', 'reverb'),

    'drivers' => [
        'reverb',
        'fcm',
    ],

    'fcm' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
        'vapid_key' => env('FIREBASE_VAPID_KEY'),
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'service_worker_url' => env('FIREBASE_SERVICE_WORKER_URL', '/firebase-messaging-sw.js'),
    ],
];
