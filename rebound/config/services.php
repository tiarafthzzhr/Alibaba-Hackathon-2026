<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    // id: Qwen LLM (Alibaba Cloud Model Studio / Bailian) untuk balasan chat AI agent
    // en: Qwen LLM (Alibaba Cloud Model Studio / Bailian) for AI agent chat replies
    'qwen' => [
        'api_key' => env('QWEN_API_KEY', env('DASHSCOPE_API_KEY')),
        'endpoint' => env('QWEN_API_ENDPOINT'),
        'model' => env('QWEN_MODEL_NAME', 'qwen-max'),
    ],

    // Atlas ATRIP is used only for sandbox flight-search integration. Booking,
    // payment, and ticketing are intentionally not called by this application.
    'atlas' => [
        'enabled' => env('ATLAS_SANDBOX_ENABLED', false),
        'base_url' => env('ATLAS_BASE_URL', 'https://sandbox.atriptech.com'),
        'access_key' => env('ATLAS_ACCESS_KEY'),
        'secret_key' => env('ATLAS_SECRET_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
