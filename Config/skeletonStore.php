<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the payment gateways for the application
    |
    */

    'payment_gateway' => [
        // Default payment gateway
        'default' => env('PAYMENT_GATEWAY', 'stripe'),

        // Stripe configuration
        'stripe' => [
            'public_key' => env('STRIPE_KEY'),
            'secret_key' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'success_url' => 'store.payment.success',
            'cancel_url' => 'store.payment.cancel',
        ],

        // PayPal configuration (example for future use)
        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'success_url' => 'store.payment.success',
            'cancel_url' => 'store.payment.cancel',
        ],

        // Add more payment gateways as needed
    ],

    // Other configuration values...
];
