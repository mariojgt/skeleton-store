<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => 'api/skeleton-store',
], function () {
    Route::get('/skeleton-store/frontend', function (Request $request) {
        return response()->json([
            'meta'  => [
                'message' => 'you made it!😎 Frontend'
            ]
        ]);
    });
});
