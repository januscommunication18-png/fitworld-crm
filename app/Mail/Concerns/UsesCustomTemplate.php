<?php

namespace App\Mail\Concerns;

use App\Http\Controllers\Host\EmailTemplateController;
use App\Models\EmailTemplate;
use App\Models\Host;

trait UsesCustomTemplate
{
    /**
     * Try to build the email from a custom template.
     * Returns $this if custom template found, or null to fall back to default.
     */
    protected function buildFromCustomTemplate(string $templateKey, Host $host, array $variables): ?static
    {
        $template = EmailTemplate::forHost($host->id)
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return null;
        }

        $subject = $template->subject;
        $body = $template->body_html;

        // Replace variables
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value ?? '', $subject);
            $body = str_replace($placeholder, $value ?? '', $body);
        }

        // Wrap in email layout
        $html = $this->wrapInLayout($body, $host);

        $this->subject($subject);
        $this->html($html);

        return $this;
    }

    protected function wrapInLayout(string $content, Host $host): string
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
}
