<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public function classes()
    {
        return view('host.schedule.classes');
    }

    public function appointments()
    {
        return view('host.schedule.appointments');
    }

    public function calendar()
    {
        return view('host.schedule.calendar');
    }
}
