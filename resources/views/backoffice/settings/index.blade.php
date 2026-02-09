@extends('backoffice.layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="space-y-6">
    {{-- Paddle Configuration --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--credit-card] size-5"></span>
                Paddle Configuration
            </h3>
        </div>
        <form action="{{ route('backoffice.settings.update.paddle') }}" method="POST">
            @csrf
            <div class="card-body space-y-4">
                <div class="alert alert-info">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <span>Paddle is used for subscription payments. Get your credentials from <a href="https://vendors.paddle.com" target="_blank" class="link">vendors.paddle.com</a></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="vendor_id">Vendor ID</label>
                        <input type="text" id="vendor_id" name="vendor_id"
                            value="{{ $settings['paddle']['vendor_id'] }}"
                            class="input w-full" placeholder="123456">
                    </div>
                    <div>
                        <label class="label-text" for="vendor_auth_code">Vendor Auth Code</label>
                        <input type="password" id="vendor_auth_code" name="vendor_auth_code"
                            class="input w-full" placeholder="Leave blank to keep current">
                    </div>
                </div>

                <div>
                    <label class="label-text" for="public_key">Public Key</label>
                    <textarea id="public_key" name="public_key" rows="3"
                        class="textarea w-full font-mono text-xs"
                        placeholder="-----BEGIN PUBLIC KEY-----"></textarea>
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="sandbox" value="1"
                        class="toggle toggle-warning"
                        {{ $settings['paddle']['sandbox'] ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Sandbox Mode</span>
                        <p class="text-xs text-base-content/60">Use Paddle sandbox environment for testing</p>
                    </div>
                </label>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-5"></span>
                    Save Paddle Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Email Configuration --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--mail] size-5"></span>
                Email Configuration
            </h3>
        </div>
        <form action="{{ route('backoffice.settings.update.mail') }}" method="POST">
            @csrf
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="mailer">Mail Driver</label>
                        <select id="mailer" name="mailer" class="select w-full">
                            <option value="smtp" {{ $settings['mail']['mailer'] === 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="postmark" {{ $settings['mail']['mailer'] === 'postmark' ? 'selected' : '' }}>Postmark</option>
                            <option value="mailgun" {{ $settings['mail']['mailer'] === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                            <option value="ses" {{ $settings['mail']['mailer'] === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            <option value="log" {{ $settings['mail']['mailer'] === 'log' ? 'selected' : '' }}>Log (Development)</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-text" for="mail_host">SMTP Host</label>
                        <input type="text" id="mail_host" name="host"
                            value="{{ $settings['mail']['host'] }}"
                            class="input w-full" placeholder="smtp.mailtrap.io">
                    </div>
                    <div>
                        <label class="label-text" for="mail_port">SMTP Port</label>
                        <input type="number" id="mail_port" name="port"
                            value="{{ $settings['mail']['port'] }}"
                            class="input w-full" placeholder="587">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="mail_username">SMTP Username</label>
                        <input type="text" id="mail_username" name="username"
                            value="{{ $settings['mail']['username'] }}"
                            class="input w-full">
                    </div>
                    <div>
                        <label class="label-text" for="mail_password">SMTP Password</label>
                        <input type="password" id="mail_password" name="password"
                            class="input w-full" placeholder="Leave blank to keep current">
                    </div>
                    <div>
                        <label class="label-text" for="mail_encryption">Encryption</label>
                        <select id="mail_encryption" name="encryption" class="select w-full">
                            <option value="tls" {{ $settings['mail']['encryption'] === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ $settings['mail']['encryption'] === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="" {{ empty($settings['mail']['encryption']) ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="from_address">From Address</label>
                        <input type="email" id="from_address" name="from_address"
                            value="{{ $settings['mail']['from_address'] }}"
                            class="input w-full" placeholder="noreply@fitcrm.com">
                    </div>
                    <div>
                        <label class="label-text" for="from_name">From Name</label>
                        <input type="text" id="from_name" name="from_name"
                            value="{{ $settings['mail']['from_name'] }}"
                            class="input w-full" placeholder="FitCRM">
                    </div>
                </div>
            </div>
            <div class="card-footer flex items-center gap-4">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-5"></span>
                    Save Mail Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Test Email --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--mail-forward] size-5"></span>
                Test Email
            </h3>
        </div>
        <form action="{{ route('backoffice.settings.test.mail') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="flex flex-col sm:flex-row gap-4">
                    <input type="email" name="email" class="input flex-1"
                        placeholder="Enter email address to test..." required>
                    <button type="submit" class="btn btn-soft btn-secondary">
                        <span class="icon-[tabler--send] size-5"></span>
                        Send Test Email
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- System Actions --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--tool] size-5"></span>
                System Actions
            </h3>
        </div>
        <div class="card-body">
            <div class="flex flex-wrap gap-4">
                <form action="{{ route('backoffice.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-soft btn-warning">
                        <span class="icon-[tabler--trash] size-5"></span>
                        Clear Application Cache
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
