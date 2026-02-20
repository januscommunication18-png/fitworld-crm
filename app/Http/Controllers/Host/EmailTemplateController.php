<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTemplateController extends Controller
{
    /**
     * Get the editable template keys that hosts can customize
     */
    public static function getEditableTemplateKeys(): array
    {
        return [
            'booking_confirmation' => [
                'name' => 'Booking Confirmation',
                'description' => 'Sent when a booking is confirmed',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'customer_email' => 'Customer\'s email address',
                    'class_name' => 'Name of the class/service',
                    'class_date' => 'Date of the class',
                    'class_time' => 'Time of the class',
                    'instructor_name' => 'Instructor\'s name',
                    'location' => 'Location/address',
                    'booking_id' => 'Booking reference ID',
                    'studio_name' => 'Your studio name',
                    'studio_phone' => 'Studio phone number',
                    'studio_email' => 'Studio email address',
                    'cancellation_policy' => 'Cancellation policy text',
                ],
            ],
            'payment_receipt' => [
                'name' => 'Payment Receipt / Invoice',
                'description' => 'Sent after successful payment',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'invoice_number' => 'Invoice number',
                    'payment_method' => 'Payment method used',
                    'amount_paid' => 'Amount paid',
                    'tax_amount' => 'Tax amount',
                    'total_amount' => 'Total amount',
                    'transaction_id' => 'Transaction ID',
                    'payment_date' => 'Date of payment',
                    'studio_name' => 'Your studio name',
                    'download_invoice_link' => 'Link to download invoice PDF',
                ],
            ],
            'waitlist_confirmation' => [
                'name' => 'Waitlist Confirmation',
                'description' => 'Sent when customer is added to a waitlist',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'class_name' => 'Name of the class',
                    'class_date' => 'Date of the class',
                    'class_time' => 'Time of the class',
                    'position_number' => 'Position on waitlist',
                    'studio_name' => 'Your studio name',
                ],
            ],
            'waitlist_spot_available' => [
                'name' => 'Waitlist Spot Available',
                'description' => 'Sent when a spot opens up',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'class_name' => 'Name of the class',
                    'class_date' => 'Date of the class',
                    'class_time' => 'Time of the class',
                    'confirm_link' => 'Link to confirm booking',
                    'expiry_time' => 'Time limit to confirm',
                    'studio_name' => 'Your studio name',
                ],
            ],
            'booking_reminder' => [
                'name' => 'Booking Reminder',
                'description' => 'Sent before a scheduled class',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'class_name' => 'Name of the class',
                    'class_date' => 'Date of the class',
                    'class_time' => 'Time of the class',
                    'instructor_name' => 'Instructor\'s name',
                    'location' => 'Location/address',
                    'studio_name' => 'Your studio name',
                ],
            ],
            'booking_cancellation' => [
                'name' => 'Booking Cancellation',
                'description' => 'Sent when a booking is cancelled',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'class_name' => 'Name of the class',
                    'class_date' => 'Date of the class',
                    'class_time' => 'Time of the class',
                    'cancellation_reason' => 'Reason for cancellation',
                    'refund_amount' => 'Refund amount (if applicable)',
                    'studio_name' => 'Your studio name',
                ],
            ],
            'membership_welcome' => [
                'name' => 'Membership Welcome',
                'description' => 'Sent when a new membership is activated',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'membership_name' => 'Membership plan name',
                    'start_date' => 'Membership start date',
                    'end_date' => 'Membership end date',
                    'benefits' => 'Membership benefits',
                    'studio_name' => 'Your studio name',
                    'portal_link' => 'Link to member portal',
                ],
            ],
            'helpdesk_reply' => [
                'name' => 'Helpdesk Reply',
                'description' => 'Sent when admin replies to a support ticket',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'ticket_id' => 'Support ticket ID',
                    'ticket_subject' => 'Ticket subject',
                    'response_message' => 'Your reply message',
                    'studio_name' => 'Your studio name',
                    'studio_signature' => 'Studio signature',
                ],
            ],
            'intake_form_request' => [
                'name' => 'Intake Form Request',
                'description' => 'Sent to request completion of intake form',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'form_name' => 'Name of the intake form',
                    'form_link' => 'Link to complete the form',
                    'due_date' => 'Deadline to complete',
                    'class_name' => 'Related class/service name',
                    'studio_name' => 'Your studio name',
                ],
            ],
        ];
    }

    /**
     * Display list of email templates
     */
    public function index()
    {
        $host = auth()->user()->currentHost();
        $editableKeys = self::getEditableTemplateKeys();

        // Get host's custom templates
        $customTemplates = EmailTemplate::forHost($host->id)
            ->whereIn('key', array_keys($editableKeys))
            ->get()
            ->keyBy('key');

        // Build template list with status
        $templates = [];
        foreach ($editableKeys as $key => $info) {
            $customTemplate = $customTemplates->get($key);

            $templates[$key] = [
                'key' => $key,
                'name' => $info['name'],
                'description' => $info['description'],
                'category' => $info['category'],
                'variables' => $info['variables'],
                'is_customized' => $customTemplate !== null,
            ];
        }

        return view('host.settings.communication.email-templates.index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show edit form for a template
     */
    public function edit(string $key)
    {
        $host = auth()->user()->currentHost();
        $editableKeys = self::getEditableTemplateKeys();

        if (!isset($editableKeys[$key])) {
            return redirect()->route('settings.communication.email-templates')
                ->with('error', 'Invalid template key.');
        }

        $templateConfig = $editableKeys[$key];

        // Get host's custom template
        $template = EmailTemplate::forHost($host->id)->where('key', $key)->first();

        // Default subject based on template type
        $defaultSubject = $this->getDefaultSubject($key);
        $defaultBody = $this->getDefaultTemplateHtml($key);

        return view('host.settings.communication.email-templates.edit', [
            'template' => $template,
            'templateConfig' => $templateConfig,
            'key' => $key,
            'defaultSubject' => $defaultSubject,
            'defaultBody' => $defaultBody,
        ]);
    }

    /**
     * Get default subject for a template key
     */
    protected function getDefaultSubject(string $key): string
    {
        $subjects = [
            'booking_confirmation' => 'Your Booking is Confirmed - {{class_name}}',
            'payment_receipt' => 'Payment Receipt - Invoice #{{invoice_number}}',
            'waitlist_confirmation' => "You're on the Waitlist - {{class_name}}",
            'waitlist_spot_available' => 'A Spot is Available! - {{class_name}}',
            'booking_reminder' => 'Reminder: {{class_name}} Tomorrow',
            'booking_cancellation' => 'Booking Cancelled - {{class_name}}',
            'membership_welcome' => 'Welcome to {{membership_name}}!',
            'helpdesk_reply' => 'Re: {{ticket_subject}}',
            'intake_form_request' => 'Please Complete Your Intake Form',
        ];

        return $subjects[$key] ?? 'Email from {{studio_name}}';
    }

    /**
     * Update/customize a template
     */
    public function update(Request $request, string $key)
    {
        $host = auth()->user()->currentHost();
        $editableKeys = self::getEditableTemplateKeys();

        if (!isset($editableKeys[$key])) {
            return back()->with('error', 'Invalid template key.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body_content' => 'required|string',
        ]);

        $templateConfig = $editableKeys[$key];

        // Find or create host's custom template
        $template = EmailTemplate::forHost($host->id)->where('key', $key)->first();

        if ($template) {
            $template->update([
                'subject' => $validated['subject'],
                'body_html' => $validated['body_content'],
            ]);
        } else {
            EmailTemplate::create([
                'host_id' => $host->id,
                'category' => $templateConfig['category'],
                'key' => $key,
                'name' => $templateConfig['name'],
                'subject' => $validated['subject'],
                'body_html' => $validated['body_content'],
                'variables' => array_keys($templateConfig['variables']),
                'is_active' => true,
            ]);
        }

        return redirect()->route('settings.communication.email-templates')
            ->with('success', 'Email template updated successfully.');
    }

    /**
     * Preview a template with sample data
     */
    public function preview(Request $request, string $key)
    {
        $host = auth()->user()->currentHost();
        $editableKeys = self::getEditableTemplateKeys();

        if (!isset($editableKeys[$key])) {
            return response()->json(['error' => 'Invalid template key.'], 400);
        }

        // Get subject and body from request, or load from template/defaults
        $subject = $request->input('subject');
        $bodyContent = $request->input('body_content');

        // If not provided, load from saved template or use defaults
        if (empty($subject) || empty($bodyContent)) {
            $template = EmailTemplate::forHost($host->id)->where('key', $key)->first();

            if ($template) {
                $subject = $subject ?: $template->subject;
                $bodyContent = $bodyContent ?: $template->body_html;
            } else {
                $subject = $subject ?: $this->getDefaultSubject($key);
                $bodyContent = $bodyContent ?: $this->getDefaultTemplateHtml($key);
            }
        }

        // Generate sample data
        $sampleData = $this->getSampleData($host, $key);

        // Render with sample data
        foreach ($sampleData as $varKey => $value) {
            $placeholder = '{{' . $varKey . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $bodyContent = str_replace($placeholder, $value, $bodyContent);
        }

        // Wrap in email layout
        $html = $this->wrapInEmailLayout($bodyContent, $host);

        return response()->json([
            'subject' => $subject,
            'html' => $html,
        ]);
    }

    /**
     * Wrap body content in email layout
     */
    protected function wrapInEmailLayout(string $content, $host): string
    {
        $studioName = $host->studio_name ?? 'Your Studio';
        $primaryColor = $host->booking_settings['primary_color'] ?? '#6366f1';

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0; background-color: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: ' . $primaryColor . '; color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 32px 24px; }
        .content h2 { color: #111827; margin-top: 0; }
        .content ul { padding-left: 20px; }
        .content a { color: ' . $primaryColor . '; }
        .footer { background: #f9fafb; padding: 24px; text-align: center; font-size: 14px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . htmlspecialchars($studioName) . '</h1>
        </div>
        <div class="content">' . $content . '</div>
        <div class="footer">
            <p>' . htmlspecialchars($studioName) . '</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Send a test email
     */
    public function sendTest(Request $request, string $key)
    {
        $host = auth()->user()->currentHost();
        $user = auth()->user();
        $editableKeys = self::getEditableTemplateKeys();

        if (!isset($editableKeys[$key])) {
            return response()->json(['error' => 'Invalid template key.'], 400);
        }

        $subject = $request->input('subject', '');
        $bodyContent = $request->input('body_content', '');

        // Generate sample data
        $sampleData = $this->getSampleData($host, $key);

        // Render with sample data
        foreach ($sampleData as $varKey => $value) {
            $placeholder = '{{' . $varKey . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $bodyContent = str_replace($placeholder, $value, $bodyContent);
        }

        // Wrap in email layout
        $html = $this->wrapInEmailLayout($bodyContent, $host);

        try {
            Mail::html($html, function ($message) use ($user, $subject, $host) {
                $message->to($user->email)
                    ->subject('[TEST] ' . $subject)
                    ->from(config('mail.from.address'), $host->studio_name ?? config('mail.from.name'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent to ' . $user->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset template to system default
     */
    public function reset(string $key)
    {
        $host = auth()->user()->currentHost();
        $editableKeys = self::getEditableTemplateKeys();

        if (!isset($editableKeys[$key])) {
            return back()->with('error', 'Invalid template key.');
        }

        // Delete host's custom template (will fall back to system default)
        EmailTemplate::forHost($host->id)->where('key', $key)->delete();

        return redirect()->route('settings.communication.email-templates')
            ->with('success', 'Email template reset to default successfully.');
    }

    /**
     * Get sample data for preview
     */
    protected function getSampleData($host, string $key): array
    {
        $user = auth()->user();

        return [
            'customer_name' => 'John Doe',
            'customer_email' => 'john.doe@example.com',
            'class_name' => 'Morning Yoga',
            'class_date' => now()->addDays(3)->format('l, F j, Y'),
            'class_time' => '9:00 AM',
            'instructor_name' => 'Sarah Johnson',
            'location' => $host->defaultLocation()?->full_address ?? '123 Main St, City',
            'booking_id' => 'BK-' . strtoupper(substr(md5(time()), 0, 8)),
            'studio_name' => $host->studio_name ?? 'Your Studio',
            'studio_phone' => $host->phone ?? '(555) 123-4567',
            'studio_email' => $host->studio_email ?? 'info@yourstudio.com',
            'cancellation_policy' => $host->getPolicy('house_rules') ?? 'Please cancel at least 24 hours in advance.',
            'invoice_number' => 'INV-' . now()->format('Ym') . '-00042',
            'payment_method' => 'Credit Card',
            'amount_paid' => '$25.00',
            'tax_amount' => '$2.50',
            'total_amount' => '$27.50',
            'transaction_id' => 'TXN_' . strtoupper(substr(md5(time()), 0, 12)),
            'payment_date' => now()->format('F j, Y'),
            'download_invoice_link' => url('/portal/invoices/sample/download'),
            'position_number' => '3',
            'confirm_link' => url('/book/confirm/sample'),
            'expiry_time' => '2 hours',
            'cancellation_reason' => 'Class cancelled by instructor',
            'refund_amount' => '$25.00',
            'membership_name' => 'Unlimited Monthly',
            'start_date' => now()->format('F j, Y'),
            'end_date' => now()->addMonth()->format('F j, Y'),
            'benefits' => 'Unlimited classes, priority booking, free guest passes',
            'portal_link' => url('/portal'),
            'ticket_id' => 'TKT-' . strtoupper(substr(md5(time()), 0, 6)),
            'ticket_subject' => 'Question about my membership',
            'response_message' => 'Thank you for reaching out! Your membership has been updated...',
            'studio_signature' => $host->contact_name ?? 'The ' . ($host->studio_name ?? 'Studio') . ' Team',
            'form_name' => 'New Member Intake Form',
            'form_link' => url('/forms/sample'),
            'due_date' => now()->addDays(2)->format('F j, Y'),
        ];
    }

    /**
     * Get default template HTML for a key
     */
    protected function getDefaultTemplateHtml(string $key): string
    {
        $defaults = [
            'booking_confirmation' => '<h2>Booking Confirmed!</h2>
<p>Hi {{customer_name}},</p>
<p>Your booking has been confirmed. Here are the details:</p>
<ul>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>Date:</strong> {{class_date}}</li>
    <li><strong>Time:</strong> {{class_time}}</li>
    <li><strong>Instructor:</strong> {{instructor_name}}</li>
    <li><strong>Location:</strong> {{location}}</li>
</ul>
<p>Booking Reference: {{booking_id}}</p>
<p>See you soon!</p>
<p>{{studio_name}}</p>',

            'payment_receipt' => '<h2>Payment Receipt</h2>
<p>Hi {{customer_name}},</p>
<p>Thank you for your payment. Here are the details:</p>
<ul>
    <li><strong>Invoice:</strong> {{invoice_number}}</li>
    <li><strong>Amount:</strong> {{total_amount}}</li>
    <li><strong>Payment Method:</strong> {{payment_method}}</li>
    <li><strong>Transaction ID:</strong> {{transaction_id}}</li>
</ul>
<p>{{studio_name}}</p>',

            'waitlist_confirmation' => '<h2>You\'re on the Waitlist</h2>
<p>Hi {{customer_name}},</p>
<p>You have been added to the waitlist for:</p>
<ul>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>Date:</strong> {{class_date}}</li>
    <li><strong>Time:</strong> {{class_time}}</li>
</ul>
<p>Your position: #{{position_number}}</p>
<p>We\'ll notify you if a spot opens up!</p>
<p>{{studio_name}}</p>',

            'waitlist_spot_available' => '<h2>A Spot is Available!</h2>
<p>Hi {{customer_name}},</p>
<p>Great news! A spot has opened up for:</p>
<ul>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>Date:</strong> {{class_date}}</li>
    <li><strong>Time:</strong> {{class_time}}</li>
</ul>
<p><a href="{{confirm_link}}">Click here to confirm your booking</a></p>
<p>Please confirm within {{expiry_time}} or the spot will go to the next person.</p>
<p>{{studio_name}}</p>',

            'booking_reminder' => '<h2>Reminder: Upcoming Class</h2>
<p>Hi {{customer_name}},</p>
<p>This is a reminder about your upcoming class:</p>
<ul>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>Date:</strong> {{class_date}}</li>
    <li><strong>Time:</strong> {{class_time}}</li>
    <li><strong>Instructor:</strong> {{instructor_name}}</li>
    <li><strong>Location:</strong> {{location}}</li>
</ul>
<p>See you soon!</p>
<p>{{studio_name}}</p>',

            'booking_cancellation' => '<h2>Booking Cancelled</h2>
<p>Hi {{customer_name}},</p>
<p>Your booking has been cancelled:</p>
<ul>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>Date:</strong> {{class_date}}</li>
    <li><strong>Time:</strong> {{class_time}}</li>
</ul>
<p>{{studio_name}}</p>',

            'membership_welcome' => '<h2>Welcome to {{membership_name}}!</h2>
<p>Hi {{customer_name}},</p>
<p>Welcome to {{studio_name}}! Your membership is now active.</p>
<ul>
    <li><strong>Plan:</strong> {{membership_name}}</li>
    <li><strong>Start Date:</strong> {{start_date}}</li>
    <li><strong>End Date:</strong> {{end_date}}</li>
</ul>
<p>Benefits: {{benefits}}</p>
<p><a href="{{portal_link}}">Access your member portal</a></p>
<p>{{studio_name}}</p>',

            'helpdesk_reply' => '<h2>Re: {{ticket_subject}}</h2>
<p>Hi {{customer_name}},</p>
<p>{{response_message}}</p>
<p>Ticket ID: {{ticket_id}}</p>
<p>Best regards,<br>{{studio_signature}}</p>',

            'intake_form_request' => '<h2>Please Complete Your Intake Form</h2>
<p>Hi {{customer_name}},</p>
<p>Please complete the following intake form before your upcoming appointment:</p>
<ul>
    <li><strong>Form:</strong> {{form_name}}</li>
    <li><strong>Related to:</strong> {{class_name}}</li>
    <li><strong>Due by:</strong> {{due_date}}</li>
</ul>
<p><a href="{{form_link}}">Click here to complete the form</a></p>
<p>{{studio_name}}</p>',
        ];

        return $defaults[$key] ?? '<p>Email content goes here...</p>';
    }
}
