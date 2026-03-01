<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Thank You - FitCRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-base-200">
    <div class="w-full max-w-lg text-center">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body py-12">
                <div class="flex justify-center mb-6">
                    <div class="bg-success/10 text-success size-20 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--check] size-10"></span>
                    </div>
                </div>

                <h2 class="card-title text-2xl justify-center mb-2">You're on the list!</h2>
                <p class="text-base-content/60 mb-6">
                    Thank you for joining the FitCRM waitlist. We'll notify you as soon as we launch with exclusive founding member pricing.
                </p>

                <div class="bg-base-200 rounded-lg p-4">
                    <p class="text-sm text-base-content/70">
                        <span class="icon-[tabler--mail] size-4 inline-block mr-1"></span>
                        Check your inbox for a confirmation email.
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-base-content/40 mt-4">
            Powered by <a href="{{ url('/') }}" class="link link-hover" target="_blank">FitCRM</a>
        </p>
    </div>
</body>
</html>
