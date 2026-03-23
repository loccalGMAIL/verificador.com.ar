<?php

return [
    'access_token'    => env('MP_ACCESS_TOKEN', ''),
    'webhook_secret'  => env('MP_WEBHOOK_SECRET', ''),
    'back_url'        => env('APP_URL', 'http://localhost') . '/dashboard/subscription/return',
    'notification_url'  => env('APP_URL', 'http://localhost') . '/webhooks/mercadopago',
    'test_payer_email'  => env('MP_TEST_PAYER_EMAIL', ''),
];
