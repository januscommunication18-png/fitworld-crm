<?php

namespace App\Observers;

use App\Mail\WelcomeMail;
use App\Models\AutomationSetting;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        // Send welcome email if automation is enabled for this host
        if (!$client->host_id || !$client->email) {
            return;
        }

        $isEnabled = AutomationSetting::isEnabledForHost($client->host_id, AutomationSetting::KEY_WELCOME_EMAIL);

        if ($isEnabled) {
            Mail::to($client->email)->queue(new WelcomeMail($client));
        }
    }
}
