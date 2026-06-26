<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Site\SiteDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, SiteDataService $site): View
    {
        return view('public.home', $site->home($request));
    }
}