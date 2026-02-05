<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        return view('host.settings.index');
    }
}
