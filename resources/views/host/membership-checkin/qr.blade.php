<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check In — {{ $membershipPlan->name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Logo / Studio Name --}}
    <div class="text-center mb-6">
        @if($host->logo_path)
            <img src="{{ Storage::disk(config('filesystems.uploads'))->url($host->logo_path) }}" alt="{{ $host->studio_name }}" class="h-12 mx-auto mb-3">
        @endif
        <h1 class="text-xl font-bold">{{ $host->studio_name }}</h1>
        <p class="text-base-content/60 text-sm mt-1">Member Check-in</p>
    </div>

    {{-- Check-in Card --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                    <span class="icon-[tabler--door-enter] size-7 text-primary"></span>
                </div>
                <h2 class="text-lg font-semibold">{{ $membershipPlan->name }}</h2>
                <p class="text-sm text-base-content/60">Enter your email to check in</p>
            </div>

            {{-- Check-in Form --}}
            <div id="checkin-form">
                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="email">Email Address</label>
                        <input type="email" id="email" class="input w-full mt-1" placeholder="your@email.com" required autofocus>
                    </div>
                    <button type="button" id="checkin-btn" class="btn btn-primary w-full" onclick="selfCheckIn()">
                        <span class="icon-[tabler--login] size-5"></span>
                        Check In
                    </button>
                </div>
            </div>

            {{-- Success State --}}
            <div id="checkin-success" class="hidden text-center py-4">
                <div class="w-16 h-16 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-3">
                    <span class="icon-[tabler--circle-check-filled] size-10 text-success"></span>
                </div>
                <h3 class="text-lg font-semibold text-success" id="success-name">Checked In!</h3>
                <p class="text-sm text-base-content/60 mt-1" id="success-time"></p>
                <p class="text-sm text-base-content/60 mt-1" id="success-credits"></p>
                <button type="button" class="btn btn-ghost btn-sm mt-4" onclick="resetForm()">
                    Check in another member
                </button>
            </div>

            {{-- Error State --}}
            <div id="checkin-error" class="hidden mt-3">
                <div class="alert alert-error alert-soft">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span id="error-message" class="text-sm"></span>
                </div>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-base-content/40 mt-4">Powered by FitCRM</p>
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function selfCheckIn() {
    var email = document.getElementById('email').value.trim();
    var btn = document.getElementById('checkin-btn');
    var errorDiv = document.getElementById('checkin-error');
    var errorMsg = document.getElementById('error-message');

    if (!email) {
        errorMsg.textContent = 'Please enter your email address.';
        errorDiv.classList.remove('hidden');
        return;
    }

    errorDiv.classList.add('hidden');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Checking in...';

    fetch('{{ route("membership-checkin.store", $membershipPlan) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            email: email,
            self_checkin: true
        })
    })
    .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
    .then(function(result) {
        if (result.ok && result.data.success) {
            document.getElementById('checkin-form').classList.add('hidden');
            document.getElementById('checkin-success').classList.remove('hidden');
            document.getElementById('success-name').textContent = result.data.checkin.client_name + ' — Checked In!';
            document.getElementById('success-time').textContent = 'Checked in at ' + result.data.checkin.checked_in_at;
            if (result.data.checkin.credits_remaining !== null && result.data.checkin.credits_remaining !== undefined) {
                document.getElementById('success-credits').textContent = result.data.checkin.credits_remaining + ' credits remaining';
            }
        } else {
            errorMsg.textContent = result.data.message || 'Check-in failed. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(function() {
        errorMsg.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<span class="icon-[tabler--login] size-5"></span> Check In';
    });
}

function resetForm() {
    document.getElementById('email').value = '';
    document.getElementById('checkin-form').classList.remove('hidden');
    document.getElementById('checkin-success').classList.add('hidden');
    document.getElementById('checkin-error').classList.add('hidden');
    document.getElementById('email').focus();
}

// Enter key submits
document.getElementById('email').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') selfCheckIn();
});
</script>
</body>
</html>
