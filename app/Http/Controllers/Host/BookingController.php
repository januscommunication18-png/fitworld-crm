<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function index()
    {
        return view('host.bookings.index');
    }
}
