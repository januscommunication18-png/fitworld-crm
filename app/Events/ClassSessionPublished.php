<?php

namespace App\Events;

use App\Models\ClassSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassSessionPublished
{
    use Dispatchable, SerializesModels;

    public ClassSession $session;

    public function __construct(ClassSession $session)
    {
        $this->session = $session;
    }
}
