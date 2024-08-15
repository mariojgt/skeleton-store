<?php

namespace Skeleton\Store\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class FrontendHomeController extends Controller
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
