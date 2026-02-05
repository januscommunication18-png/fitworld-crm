<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class SignupController extends Controller
{
    public function index()
    {
        return view('host.signup');
    }
}
