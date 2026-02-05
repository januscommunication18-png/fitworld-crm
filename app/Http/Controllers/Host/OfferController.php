<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class OfferController extends Controller
{
    public function index()
    {
        return view('host.offers.index');
    }
}
