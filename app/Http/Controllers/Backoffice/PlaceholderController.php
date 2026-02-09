<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;

class PlaceholderController extends Controller
{
    public function class()
    {
        return view('backoffice.placeholders.class');
    }

    public function bookings()
    {
        return view('backoffice.placeholders.bookings');
    }

    public function schedule()
    {
        return view('backoffice.placeholders.schedule');
    }

    public function members()
    {
        return view('backoffice.placeholders.members');
    }

    public function invoice()
    {
        return view('backoffice.placeholders.invoice');
    }
}
