<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'url' => env('WHATSAPP_API_URL'),
        'key' => env('WHATSAPP_API_KEY'),
        'session_id' => env('WHATSAPP_SESSION_ID', 'default'),
    ],

    'waha' => [
        'url' => env('WAHA_API_URL', 'http://localhost:3000'),
        'api_url' => env('WAHA_API_URL', 'http://localhost:3000'),
        'api_key' => env('WAHA_API_KEY', env('WAHA_API_TOKEN')),
        'api_token' => env('WAHA_API_TOKEN', env('WAHA_API_KEY')),
        'webhook_secret' => env('WAHA_WEBHOOK_SECRET'),
        'session' => env('WAHA_SESSION', 'default'),
    ],

    'ragflow' => [
        'url' => env('RAGFLOW_API_URL', 'http://localhost:80'),
        'api_key' => env('RAGFLOW_API_KEY'),
    ],

    'pinecone' => [
        'key' => env('PINECONE_API_KEY'),
        'environment' => env('PINECONE_ENVIRONMENT'),
        'index' => env('PINECONE_INDEX_NAME'),
    ],

    'pushbullet' => [
        'key' => env('PUSHBULLET_API_KEY'),
    ],

    'ai' => [
        'verify_ssl' => env('AI_VERIFY_SSL', true),
        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
        ],
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
        ],
        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
        ],
        'groq' => [
            'key' => env('GROQ_API_KEY'),
        ],
    ],

    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY', ''),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN', ''),
        'project_id' => env('FIREBASE_PROJECT_ID', ''),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', ''),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', ''),
        'app_id' => env('FIREBASE_APP_ID', ''),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID', ''),
        'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json')),
    ],

];
