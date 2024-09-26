<?php

return [
    'payment_gateway' => [
        'stripe' => [
            'secret'      => env('STRIPE_SECRET'),
            'public'      => env('STRIPE_PUBLIC'),
            'success_url' => 'home',
            'cancel_url'  => 'home',
        ]
    ],
];
