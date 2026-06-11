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
                'description' => 'Sent when a booking is confirmed and payment has been received',
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
                    'qr_image_url' => 'Client check-in QR code image URL',
                    'qr_download_url' => 'Client check-in QR code download link',
                    'studio_name' => 'Your studio name',
                    'studio_phone' => 'Studio phone number',
                    'studio_email' => 'Studio email address',
                    'cancellation_policy' => 'Cancellation policy text',
                ],
            ],
            'client_qr_code' => [
                'name' => 'Check-In QR Code',
                'description' => 'Sent manually from a client profile to give the client their personal check-in QR code',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'customer_email' => 'Customer\'s email address',
                    'qr_image_url' => 'Client check-in QR code image (inline)',
                    'qr_download_url' => 'Client check-in QR code download link',
                    'studio_name' => 'Your studio name',
                    'studio_phone' => 'Studio phone number',
                    'studio_email' => 'Studio email address',
                ],
            ],
            'booking_received' => [
                'name' => 'Booking Received (Pending Payment)',
                'description' => 'Sent when a booking is created but payment is still pending (e.g., manual / cash / bank transfer)',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'customer_email' => 'Customer\'s email address',
                    'booking_id' => 'Booking reference ID',
                    'class_name' => 'Name of the class/service',
                    'class_date' => 'Date of the class',
                    'class_time' => 'Time of the class',
                    'instructor_name' => 'Instructor\'s name',
                    'location' => 'Location/address',
                    'transaction_id' => 'Transaction reference ID',
                    'payment_method' => 'Payment method chosen (e.g., Cash, Bank Transfer)',
                    'total_amount' => 'Total amount due',
                    'payment_instructions' => 'How the customer should pay',
                    'studio_name' => 'Your studio name',
                    'studio_phone' => 'Studio phone number',
                    'studio_email' => 'Studio email address',
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
                    'ticket_url' => 'Link to the conversation (portal deep-link or secure guest link)',
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
            'welcome_email' => [
                'name' => 'Welcome Email',
                'description' => 'Automated email sent to new clients when they sign up',
                'category' => 'automation',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'studio_name' => 'Your studio name',
                    'studio_email' => 'Studio email address',
                    'studio_phone' => 'Studio phone number',
                    'booking_url' => 'Link to book a class',
                ],
            ],
            'class_reminder' => [
                'name' => 'Class Reminder',
                'description' => 'Automated reminder sent before a scheduled class',
                'category' => 'automation',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'class_name' => 'Name of the class/service',
                    'class_date' => 'Date of the class',
                    'class_time' => 'Time of the class',
                    'instructor_name' => 'Instructor\'s name',
                    'location' => 'Location/address',
                    'studio_name' => 'Your studio name',
                    'studio_email' => 'Studio email address',
                    'studio_phone' => 'Studio phone number',
                ],
            ],
            'winback_campaign' => [
                'name' => 'Win-back Campaign',
                'description' => 'Automated email sent to inactive clients to encourage their return',
                'category' => 'automation',
                'variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'last_visit_date' => 'Date of last visit/booking',
                    'studio_name' => 'Your studio name',
                    'booking_url' => 'Link to book a class',
                ],
            ],
            'member_activation_code' => [
                'name' => 'Member Verification Code',
                'description' => 'Sent to clients during sign-up / sign-in with their one-time verification code',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Customer\'s first name',
                    'verification_code' => 'The 6-digit one-time code',
                    'expiry_minutes' => 'Minutes until the code expires',
                    'studio_name' => 'Your studio name',
                    'studio_email' => 'Studio email address',
                ],
            ],
            'class_request_received' => [
                'name' => 'Class Request Received',
                'description' => 'Sent to the requester after they submit a class info request on the public booking page',
                'category' => 'transactional',
                'variables' => [
                    'customer_name' => 'Requester\'s full name',
                    'class_name' => 'Name of the class they\'re asking about',
                    'message' => 'The note the requester left (may be empty)',
                    'studio_name' => 'Your studio name',
                    'studio_email' => 'Studio email address',
                    'studio_phone' => 'Studio phone number',
                ],
            ],
            'class_request_team_notification' => [
                'name' => 'Class Request — Team Notification',
                'description' => 'Sent to the team members selected in a class plan\'s Email Workflow when a new class request arrives',
                'category' => 'team_notification',
                'variables' => [
                    'team_member_name' => 'Team member\'s name (recipient)',
                    'customer_name' => 'Requester\'s full name',
                    'customer_email' => 'Requester\'s email',
                    'customer_phone' => 'Requester\'s phone',
                    'class_name' => 'Name of the class they\'re asking about',
                    'message' => 'The note the requester left (may be empty)',
                    'waitlist_requested' => 'Yes / No depending on whether they ticked the waitlist box',
                    'studio_name' => 'Your studio name',
                ],
            ],
            'helpdesk_assigned_team' => [
                'name' => 'Helpdesk Ticket Assigned — Team Notification',
                'description' => 'Sent to a team member when a helpdesk ticket is assigned to them',
                'category' => 'team_notification',
                'variables' => [
                    'team_member_name' => 'Team member\'s name (recipient)',
                    'ticket_id' => 'Ticket reference ID',
                    'ticket_subject' => 'Ticket subject line',
                    'customer_name' => 'Customer who opened the ticket',
                    'customer_email' => 'Customer\'s email',
                    'ticket_url' => 'Direct link to the ticket in the dashboard',
                    'studio_name' => 'Your studio name',
                ],
            ],
            'helpdesk_customer_reply' => [
                'name' => 'Helpdesk — Customer Replied (Team Notification)',
                'description' => 'Sent to the ticket\'s assigned team member when the client replies from the member portal',
                'category' => 'team_notification',
                'variables' => [
                    'team_member_name' => 'Team member\'s name (recipient)',
                    'customer_name' => 'Customer who replied',
                    'customer_email' => 'Customer\'s email',
                    'ticket_id' => 'Ticket reference ID',
                    'ticket_subject' => 'Ticket subject line',
                    'customer_message' => 'The HTML body of the customer\'s reply',
                    'ticket_url' => 'Direct link to the ticket in the dashboard',
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
            'host' => $host,
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
        $defaultSubject = self::getDefaultSubject($key);
        $defaultBody = self::getDefaultTemplateHtml($key);

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
    public static function getDefaultSubject(string $key): string
    {
        $subjects = [
            'booking_confirmation' => 'Your Booking is Confirmed - {{class_name}}',
            'client_qr_code' => 'Your Check-In QR Code - {{studio_name}}',
            'booking_received' => 'Booking Received — Payment Required ({{class_name}})',
            'payment_receipt' => 'Payment Receipt - Invoice #{{invoice_number}}',
            'waitlist_confirmation' => "You're on the Waitlist - {{class_name}}",
            'waitlist_spot_available' => 'A Spot is Available! - {{class_name}}',
            'booking_reminder' => 'Reminder: {{class_name}} Tomorrow',
            'booking_cancellation' => 'Booking Cancelled - {{class_name}}',
            'membership_welcome' => 'Welcome to {{membership_name}}!',
            'helpdesk_reply' => 'Re: {{ticket_subject}}',
            'intake_form_request' => 'Please Complete Your Intake Form',
            'welcome_email' => 'Welcome to {{studio_name}}!',
            'class_reminder' => 'Reminder: {{class_name}} - Tomorrow',
            'winback_campaign' => 'We miss you at {{studio_name}}!',
            'member_activation_code' => 'Your Verification Code - {{studio_name}}',
            'class_request_received' => 'We got your request — {{class_name}}',
            'class_request_team_notification' => 'New class request: {{class_name}} from {{customer_name}}',
            'helpdesk_assigned_team' => 'Ticket assigned to you: {{ticket_subject}}',
            'helpdesk_customer_reply' => 'Re: {{ticket_subject}} (customer reply)',
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
                $subject = $subject ?: self::getDefaultSubject($key);
                $bodyContent = $bodyContent ?: self::getDefaultTemplateHtml($key);
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
     * Update global email header & footer layout
     */
    public function updateLayout(Request $request)
    {
        $request->validate([
            'email_header_html' => 'nullable|string|max:5000',
            'email_footer_html' => 'nullable|string|max:5000',
            'email_header_show_logo' => 'nullable|boolean',
        ]);

        $host = auth()->user()->currentHost();
        $settings = $host->booking_settings ?? [];
        $settings['email_header_html'] = $request->input('email_header_html', '');
        $settings['email_footer_html'] = $request->input('email_footer_html', '');
        $settings['email_header_show_logo'] = $request->boolean('email_header_show_logo');
        $host->booking_settings = $settings;
        $host->save();

        return back()->with('success', 'Email layout updated successfully.');
    }

    /**
     * Wrap body content in email layout
     */
    protected function wrapInEmailLayout(string $content, $host): string
    {
        $studioName = $host->studio_name ?? 'Your Studio';
        $primaryColor = $host->booking_settings['primary_color'] ?? '#6366f1';

        $customHeader = $host->booking_settings['email_header_html'] ?? '';
        $customFooter = $host->booking_settings['email_footer_html'] ?? '';
        $showLogo = (bool) ($host->booking_settings['email_header_show_logo'] ?? false);
        $logoUrl = $host->logo_url ?? null;

        $headerInner = !empty($customHeader)
            ? $customHeader
            : '<h1 style="margin:0;font-size:24px;font-weight:600;">' . htmlspecialchars($studioName) . '</h1>';

        $logoHtml = ($showLogo && $logoUrl)
            ? '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . htmlspecialchars($studioName) . '" style="max-height:48px;max-width:200px;display:block;margin:0 auto 12px;">'
            : '';

        $headerHtml = $logoHtml . $headerInner;

        $footerHtml = !empty($customFooter)
            ? $customFooter
            : '<p>' . htmlspecialchars($studioName) . '</p>';

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0; background-color: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { padding: 24px; text-align: center; color: #111827; }
        .content { padding: 32px 24px; }
        .content h2 { color: #111827; margin-top: 0; }
        .content ul { padding-left: 20px; }
        .content a { color: ' . $primaryColor . '; }
        .footer { padding: 24px; text-align: center; font-size: 14px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">' . $headerHtml . '</div>
        <div class="content">' . $content . '</div>
        <div class="footer">' . $footerHtml . '</div>
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
            'payment_instructions' => 'Please complete your payment using the method you selected to secure your booking. Contact the studio if you need payment details.',
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
            'booking_url' => $host->subdomain ? url('//' . $host->subdomain . '.' . config('app.booking_domain', 'fitcrm.biz')) : url('/'),
            'last_visit_date' => now()->subDays(45)->format('F j, Y'),
            'verification_code' => '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 24px auto;">'
                . '<tr><td style="background:#f3f4f6; border-radius:8px; padding:16px 28px; font-family:Menlo,Consolas,monospace; font-size:32px; font-weight:700; letter-spacing:10px; color:#111827; text-align:center;">482915</td></tr></table>',
            'expiry_minutes' => (string) ($host->member_portal_settings['activation_code_expiry_minutes'] ?? 10),
            'team_member_name' => $user?->name ?? 'Jane Coach',
            'customer_email' => 'john.doe@example.com',
            'customer_phone' => '(555) 123-4567',
            'waitlist_requested' => 'No',
            'message' => 'I would like to try this class — what beginner times work best?',
            'ticket_url' => $host->subdomain
                ? url('//' . $host->subdomain . '.' . config('app.booking_domain', 'fitcrm.biz') . '/portal/helpdesk/0')
                : url('/helpdesk/0'),
            'customer_message' => '<p>Hi! Quick follow-up — is the 6:30 PM class still available?</p>',
        ];
    }

    /**
     * Get default template HTML for a key
     */
    public static function getDefaultTemplateHtml(string $key): string
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
<div style="text-align:center;margin:24px 0;">
    <p style="font-weight:bold;margin-bottom:8px;">Your Check-In QR Code</p>
    <p style="margin:0 0 12px;color:#6b7280;font-size:14px;">Show this at the studio to check in. It works for all your bookings, services, and memberships.</p>
    <img src="{{qr_image_url}}" alt="Your check-in QR code" width="200" height="200" style="border:1px solid #e5e7eb;border-radius:12px;padding:8px;background:#ffffff;">
    <p style="margin-top:12px;"><a href="{{qr_download_url}}">Download QR Code</a></p>
</div>
<p>See you soon!</p>
<p>{{studio_name}}</p>',

            'client_qr_code' => '<h2>Your Check-In QR Code</h2>
<p>Hi {{customer_name}},</p>
<p>Here is your personal check-in QR code for {{studio_name}}. Show it at the studio to check in — it works for all your bookings, services, and memberships.</p>
<div style="text-align:center;margin:24px 0;">
    <img src="{{qr_image_url}}" alt="Your check-in QR code" width="220" height="220" style="border:1px solid #e5e7eb;border-radius:12px;padding:8px;background:#ffffff;">
    <p style="margin-top:12px;"><a href="{{qr_download_url}}">Download QR Code</a></p>
</div>
<p>Keep this handy on your phone for a quick check-in. See you soon!</p>
<p>{{studio_name}}</p>',

            'booking_received' => '<h2>Booking Received</h2>
<p>Hi {{customer_name}},</p>
<p>We\'ve received your booking request. Please complete payment to secure your spot.</p>
<ul>
    <li><strong>Booking ID:</strong> {{booking_id}}</li>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>Date:</strong> {{class_date}}</li>
    <li><strong>Time:</strong> {{class_time}}</li>
    <li><strong>Instructor:</strong> {{instructor_name}}</li>
    <li><strong>Location:</strong> {{location}}</li>
    <li><strong>Amount Due:</strong> {{total_amount}}</li>
    <li><strong>Payment Method:</strong> {{payment_method}}</li>
    <li><strong>Transaction ID:</strong> {{transaction_id}}</li>
</ul>
<p>{{payment_instructions}}</p>
<p>If you have any questions, reply to this email or call us at {{studio_phone}}.</p>
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
<p style="margin:24px 0 8px;"><a href="{{ticket_url}}" style="display:inline-block;padding:10px 18px;background:#6366f1;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Open conversation</a></p>
<p style="font-size:12px;color:#6b7280;margin:0 0 4px;">Or copy this link into your browser:</p>
<p style="font-size:12px;color:#6366f1;word-break:break-all;margin:0 0 16px;"><a href="{{ticket_url}}" style="color:#6366f1;">{{ticket_url}}</a></p>
<p style="font-size:12px;color:#6b7280;">Ticket ID: {{ticket_id}}</p>
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

            'welcome_email' => '<h2>Welcome to {{studio_name}}!</h2>
<p>Hi {{customer_name}},</p>
<p>Thank you for joining {{studio_name}}! We\'re excited to have you as part of our community.</p>
<p>Here are a few things to get started:</p>
<ul>
    <li>Browse our class schedule and book your first session</li>
    <li>Check out our membership plans for the best value</li>
    <li>Don\'t hesitate to reach out if you have any questions</li>
</ul>
<p><a href="{{booking_url}}">Book Your First Class</a></p>
<p>See you soon!</p>
<p>{{studio_name}}<br>{{studio_email}}<br>{{studio_phone}}</p>',

            'class_reminder' => '<h2>Reminder: Upcoming Class</h2>
<p>Hi {{customer_name}},</p>
<p>This is a friendly reminder about your upcoming class:</p>
<ul>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>Date:</strong> {{class_date}}</li>
    <li><strong>Time:</strong> {{class_time}}</li>
    <li><strong>Instructor:</strong> {{instructor_name}}</li>
    <li><strong>Location:</strong> {{location}}</li>
</ul>
<p>Please arrive a few minutes early. If you need to cancel, please do so at least 24 hours in advance.</p>
<p>See you soon!</p>
<p>{{studio_name}}</p>',

            'winback_campaign' => '<h2>We Miss You!</h2>
<p>Hi {{customer_name}},</p>
<p>We noticed it\'s been a while since your last visit on {{last_visit_date}}. We\'d love to see you back at {{studio_name}}!</p>
<p>Whether you\'re looking to jump back into your routine or try something new, we have classes and sessions waiting for you.</p>
<p><a href="{{booking_url}}">Book a Class Now</a></p>
<p>We hope to see you soon!</p>
<p>{{studio_name}}</p>',

            'member_activation_code' => '<h2>Your Verification Code</h2>
<p>Hi {{customer_name}},</p>
<p>You requested to sign in to your {{studio_name}} member portal. Use the code below to verify your identity:</p>
<div style="text-align:center; margin:24px 0;">
    <div style="display:inline-block; background:#f3f4f6; border-radius:8px; padding:16px 24px; font-size:32px; font-weight:bold; letter-spacing:8px; font-family:monospace;">{{verification_code}}</div>
</div>
<p>This code will expire in <strong>{{expiry_minutes}} minutes</strong>.</p>
<p>If you didn\'t request this code, you can safely ignore this email.</p>
<p>Thanks,<br>{{studio_name}}</p>',

            'class_request_received' => '<h2>Thanks for reaching out!</h2>
<p>Hi {{customer_name}},</p>
<p>We\'ve received your request about <strong>{{class_name}}</strong>. A member of our team will follow up with you shortly.</p>
<p><strong>Your message:</strong></p>
<blockquote style="margin:8px 0;padding:8px 12px;border-left:3px solid #6366f1;background:#f9fafb;">{{message}}</blockquote>
<p>If you need to reach us before then, reply to this email or call us at {{studio_phone}}.</p>
<p>Thanks,<br>{{studio_name}}<br>{{studio_email}}</p>',

            'class_request_team_notification' => '<h2>New class request</h2>
<p>Hi {{team_member_name}},</p>
<p>A new class info-request just came in from the public booking page. Details:</p>
<ul>
    <li><strong>Class:</strong> {{class_name}}</li>
    <li><strong>From:</strong> {{customer_name}}</li>
    <li><strong>Email:</strong> {{customer_email}}</li>
    <li><strong>Phone:</strong> {{customer_phone}}</li>
    <li><strong>Waitlist requested:</strong> {{waitlist_requested}}</li>
</ul>
<p><strong>Their message:</strong></p>
<blockquote style="margin:8px 0;padding:8px 12px;border-left:3px solid #6366f1;background:#f9fafb;">{{message}}</blockquote>
<p>Please follow up with them as soon as you can.</p>
<p>— {{studio_name}}</p>',

            'helpdesk_assigned_team' => '<h2>Ticket assigned to you</h2>
<p>Hi {{team_member_name}},</p>
<p>A helpdesk ticket has just been assigned to you. Details:</p>
<ul>
    <li><strong>Ticket:</strong> #{{ticket_id}} — {{ticket_subject}}</li>
    <li><strong>From:</strong> {{customer_name}}</li>
    <li><strong>Email:</strong> {{customer_email}}</li>
</ul>
<p><a href="{{ticket_url}}">Open the ticket</a> to review and reply.</p>
<p>— {{studio_name}}</p>',

            'helpdesk_customer_reply' => '<h2>Customer reply received</h2>
<p>Hi {{team_member_name}},</p>
<p><strong>{{customer_name}}</strong> just replied on ticket #{{ticket_id}} — <em>{{ticket_subject}}</em>.</p>
<blockquote style="margin:8px 0;padding:8px 12px;border-left:3px solid #6366f1;background:#f9fafb;">{{customer_message}}</blockquote>
<p><a href="{{ticket_url}}">Open the ticket</a> to respond.</p>
<p>— {{studio_name}}</p>',
        ];

        return $defaults[$key] ?? '<p>Email content goes here...</p>';
    }
}
