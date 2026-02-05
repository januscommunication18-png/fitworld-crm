<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('host.dashboard');
    }
}
