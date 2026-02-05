<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        return view('host.reports.index');
    }
}
