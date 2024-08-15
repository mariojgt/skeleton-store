<?php

namespace Skeleton\Store\Controllers\Backend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class BackendHomeController extends Controller
{
    /**
     * @return [blade view]
     */
    public function index()
    {
        return Inertia::render('Vendor/skeleton-store/index', [
            'title'          => 'Home'
        ]);
    }
}
