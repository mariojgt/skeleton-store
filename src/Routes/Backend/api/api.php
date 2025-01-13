<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => 'api/skeleton-store',
], function () {
    Route::get('/skeleton-store/backend', function (Request $request) {
        return response()->json([
            'meta'  => [
                'message' => 'you made it!😎 Backend'
            ]
        ]);
    });
});
