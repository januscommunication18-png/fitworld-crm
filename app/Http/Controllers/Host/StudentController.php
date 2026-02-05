<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    public function index()
    {
        return view('host.students.index');
    }
}
