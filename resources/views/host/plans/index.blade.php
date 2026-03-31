@extends('layouts.dashboard')

@section('title', 'Plans & Pricing')

@section('content')
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold mb-3">Choose Your Plan</h1>
            <p class="text-base-content/70 max-w-xl mx-auto">
                Select the plan that best fits your studio's needs. All plans include core features with unlimited growth potential.
            </p>
        </div>

        {{-- Billing toggle --}}
        <div class="flex items-center justify-center gap-3 mb-8">
            <span class="text-sm font-medium" id="monthly-label">Monthly</span>
            <input type="checkbox" class="toggle toggle-primary" id="billing-toggle">
            <span class="text-sm font-medium" id="yearly-label">
                Yearly
                <span class="badge badge-success badge-sm ml-1">Save 20%</span>
            </span>
        </div>

        {{-- Plans grid --}}
        <div class="grid md:grid-cols-3 gap-6 mb-10">
            {{-- Starter Plan --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">Starter</h2>
                    <p class="text-base-content/60 text-sm">Perfect for solo instructors</p>

                    <div class="my-4">
                        <span class="text-4xl font-bold" data-monthly="$29" data-yearly="$23">$29</span>
                        <span class="text-base-content/60">/month</span>
                    </div>

                    <ul class="space-y-2 text-sm mb-6">
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Up to 50 active clients
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            1 instructor
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Online booking
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Payment processing
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Email reminders
                        </li>
                    </ul>

                    <button class="btn btn-outline btn-primary w-full" data-plan="starter">
                        Get Started
                    </button>
                </div>
            </div>

            {{-- Professional Plan (Popular) --}}
            <div class="card bg-base-100 border-2 border-primary relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="badge badge-primary">Most Popular</span>
                </div>
                <div class="card-body">
                    <h2 class="card-title text-lg">Professional</h2>
                    <p class="text-base-content/60 text-sm">For growing studios</p>

                    <div class="my-4">
                        <span class="text-4xl font-bold" data-monthly="$79" data-yearly="$63">$79</span>
                        <span class="text-base-content/60">/month</span>
                    </div>

                    <ul class="space-y-2 text-sm mb-6">
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Up to 500 active clients
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Up to 5 instructors
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            All Starter features
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Memberships & packages
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Custom branding
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Priority support
                        </li>
                    </ul>

                    <button class="btn btn-primary w-full" data-plan="professional">
                        Get Started
                    </button>
                </div>
            </div>

            {{-- Enterprise Plan --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">Enterprise</h2>
                    <p class="text-base-content/60 text-sm">For multi-location studios</p>

                    <div class="my-4">
                        <span class="text-4xl font-bold" data-monthly="$199" data-yearly="$159">$199</span>
                        <span class="text-base-content/60">/month</span>
                    </div>

                    <ul class="space-y-2 text-sm mb-6">
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Unlimited clients
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Unlimited instructors
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            All Professional features
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Multiple locations
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            API access
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--check] size-4 text-success"></span>
                            Dedicated support
                        </li>
                    </ul>

                    <button class="btn btn-outline btn-primary w-full" data-plan="enterprise">
                        Contact Sales
                    </button>
                </div>
            </div>
        </div>

        {{-- FAQ or additional info --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="font-semibold mb-3">Need Help Choosing?</h3>
                <p class="text-sm text-base-content/70 mb-4">
                    Not sure which plan is right for you? Our team is here to help you find the perfect fit for your studio.
                </p>
                <a href="mailto:sales@{{ config('app.domain', 'fitcrm.com') }}" class="btn btn-soft btn-primary btn-sm">
                    <span class="icon-[tabler--mail] size-4 mr-2"></span>
                    Contact Sales
                </a>
            </div>
        </div>
    </div>

    <script>
        // Billing toggle functionality
        document.getElementById('billing-toggle')?.addEventListener('change', function() {
            const isYearly = this.checked;
            const prices = document.querySelectorAll('[data-monthly]');

            prices.forEach(price => {
                price.textContent = isYearly ? price.dataset.yearly : price.dataset.monthly;
            });
        });

        // Plan selection (placeholder - will integrate with Stripe)
        document.querySelectorAll('[data-plan]').forEach(btn => {
            btn.addEventListener('click', function() {
                const plan = this.dataset.plan;
                const isYearly = document.getElementById('billing-toggle').checked;

                // TODO: Integrate with Stripe checkout
                console.log('Selected plan:', plan, 'Billing:', isYearly ? 'yearly' : 'monthly');
                alert('Plan selection coming soon! Selected: ' + plan);
            });
        });
    </script>
@endsection
