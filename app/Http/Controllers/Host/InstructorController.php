<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class InstructorController extends Controller
{
    public function index()
    {
        return view('host.instructors.index');
    }
}
