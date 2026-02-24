<?php

namespace App\Events;

use App\Models\CustomerMembership;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MembershipActivated
{
    use Dispatchable, SerializesModels;

    public CustomerMembership $membership;

    public function __construct(CustomerMembership $membership)
    {
        $this->membership = $membership;
    }
}
