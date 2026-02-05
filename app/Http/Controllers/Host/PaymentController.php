<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function transactions()
    {
        return view('host.payments.transactions');
    }

    public function memberships()
    {
        return view('host.payments.memberships');
    }

    public function classPacks()
    {
        return view('host.payments.class-packs');
    }
}
