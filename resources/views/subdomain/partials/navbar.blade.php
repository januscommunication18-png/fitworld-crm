@php
    // Currency setup
    $hostCurrencies = $host->currencies ?? [$host->default_currency ?? 'USD'];
    if (!is_array($hostCurrencies)) {
        $hostCurrencies = [$host->default_currency ?? 'USD'];
    }
    $selectedCurrency = session("currency_{$host->id}", $host->default_currency ?? 'USD');
    $currencySymbols = \App\Models\MembershipPlan::getCurrencySymbols();
    $hasMultipleCurrencies = count($hostCurrencies) > 1;

    // Language setup
    $hostLanguages = $host->studio_languages ?? [$host->default_language_booking ?? 'en'];
    if (!is_array($hostLanguages) || empty($hostLanguages)) {
        $hostLanguages = ['en'];
    }
    $selectedLanguage = session("language_{$host->id}", $host->default_language_booking ?? 'en');
    $languageNames = ['en' => 'English', 'fr' => 'Français', 'de' => 'Deutsch', 'es' => 'Español'];
    $languageFlags = ['en' => '🇺🇸', 'fr' => '🇫🇷', 'de' => '🇩🇪', 'es' => '🇪🇸'];
    $hasMultipleLanguages = count($hostLanguages) > 1;
@endphp

{{-- Navigation Bar - 75px height --}}
<nav class="bg-base-100 border-b border-base-200 sticky top-0 z-40" style="height: 75px;">
    <div class="container-fixed h-full">
        <div class="flex items-center justify-between h-full">
            {{-- Left: Logo --}}
            <div class="flex items-center">
                @if($host->logo_url)
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-auto max-w-[180px] object-contain">
                    </a>
                @else
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-lg font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                        </div>
                        <span class="font-bold text-lg hidden sm:inline">{{ $host->studio_name }}</span>
                    </a>
                @endif
            </div>

            {{-- Right: Language Picker + Currency Picker + Request Booking + Member Login --}}
            <div class="flex items-center gap-3">
                {{-- Language Picker (only show if multiple languages) --}}
                @if($hasMultipleLanguages)
                <div class="relative" id="language-dropdown">
                    <button type="button"
                            class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg hover:bg-base-200 transition-colors"
                            id="language-picker-btn">
                        <span id="current-language-flag">{{ $languageFlags[$selectedLanguage] ?? '🌐' }}</span>
                        <span id="current-language-code" class="hidden sm:inline">{{ strtoupper($selectedLanguage) }}</span>
                        <svg class="w-4 h-4 transition-transform" id="language-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="language-menu"
                         class="hidden absolute right-0 mt-2 w-48 bg-base-100 rounded-lg shadow-lg border border-base-200 py-1 z-50">
                        <div class="px-3 py-2 text-xs text-base-content/60 border-b border-base-200">Select Language</div>
                        @foreach($hostLanguages as $lang)
                            <button type="button"
                                    class="language-option w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-base-200 transition-colors {{ $lang === $selectedLanguage ? 'bg-primary/10' : '' }}"
                                    data-language="{{ $lang }}"
                                    data-flag="{{ $languageFlags[$lang] ?? '🌐' }}"
                                    data-name="{{ $languageNames[$lang] ?? $lang }}">
                                <span class="w-6">{{ $languageFlags[$lang] ?? '🌐' }}</span>
                                <span>{{ $languageNames[$lang] ?? $lang }}</span>
                                @if($lang === $selectedLanguage)
                                    <svg class="w-4 h-4 ml-auto text-success language-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="w-px h-6 bg-base-300 hidden sm:block"></div>
                @endif

                {{-- Currency Picker (only show if multiple currencies) --}}
                @if($hasMultipleCurrencies)
                <div class="relative" id="currency-dropdown">
                    <button type="button"
                            class="flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-lg hover:bg-base-200 transition-colors"
                            id="currency-picker-btn">
                        <span class="font-bold text-primary" id="current-currency-symbol">{{ $currencySymbols[$selectedCurrency] ?? '$' }}</span>
                        <span id="current-currency-code">{{ $selectedCurrency }}</span>
                        <svg class="w-4 h-4 transition-transform" id="currency-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="currency-menu"
                         class="hidden absolute right-0 mt-2 w-48 bg-base-100 rounded-lg shadow-lg border border-base-200 py-1 z-50">
                        <div class="px-3 py-2 text-xs text-base-content/60 border-b border-base-200">Select Currency</div>
                        @foreach($hostCurrencies as $currency)
                            <button type="button"
                                    class="currency-option w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-base-200 transition-colors {{ $currency === $selectedCurrency ? 'bg-primary/10' : '' }}"
                                    data-currency="{{ $currency }}"
                                    data-symbol="{{ $currencySymbols[$currency] ?? $currency }}">
                                <span class="font-bold text-primary w-6">{{ $currencySymbols[$currency] ?? '' }}</span>
                                <span>{{ $currency }}</span>
                                @if($currency === $selectedCurrency)
                                    <svg class="w-4 h-4 ml-auto text-success currency-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="w-px h-6 bg-base-300 hidden sm:block"></div>
                @endif
                {{-- Request Booking Button --}}
                <a href="{{ route('subdomain.service-request', ['subdomain' => $host->subdomain]) }}"
                   class="btn btn-primary btn-sm sm:btn-md">
                    <span class="icon-[tabler--calendar-plus] size-5 hidden sm:inline"></span>
                    Request Booking
                </a>

                {{-- Member Login --}}
                @if($host->isMemberPortalEnabled())
                    @if(auth('member')->check())
                        {{-- Already logged in --}}
                        <a href="{{ route('member.portal', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-ghost btn-sm sm:btn-md">
                            <span class="icon-[tabler--user] size-5"></span>
                            <span class="hidden sm:inline">My Portal</span>
                        </a>
                    @else
                        <a href="{{ route('member.login', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-ghost btn-sm sm:btn-md">
                            <span class="icon-[tabler--login] size-5"></span>
                            <span class="hidden sm:inline">Member Login</span>
                        </a>
                    @endif
                @else
                    <div class="relative group">
                        <button class="btn btn-ghost btn-sm sm:btn-md" disabled>
                            <span class="icon-[tabler--login] size-5"></span>
                            <span class="hidden sm:inline">Member Login</span>
                        </button>
                        <div class="absolute top-full right-0 mt-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            <span class="badge badge-sm badge-neutral whitespace-nowrap">Coming Soon</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>

{{-- Currency Picker JavaScript --}}
@if($hasMultipleCurrencies)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pickerBtn = document.getElementById('currency-picker-btn');
    const menu = document.getElementById('currency-menu');
    const chevron = document.getElementById('currency-chevron');
    const currencyOptions = document.querySelectorAll('.currency-option');
    const currencySymbolEl = document.getElementById('current-currency-symbol');
    const currencyCodeEl = document.getElementById('current-currency-code');

    // Toggle dropdown
    pickerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && !pickerBtn.contains(e.target)) {
            menu.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    });

    // Handle currency selection
    currencyOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            const currency = this.dataset.currency;
            const symbol = this.dataset.symbol;

            // Update UI immediately
            currencySymbolEl.textContent = symbol;
            currencyCodeEl.textContent = currency;

            // Update active state
            currencyOptions.forEach(opt => {
                opt.classList.remove('bg-primary/10');
                const checkIcon = opt.querySelector('.currency-check');
                if (checkIcon) checkIcon.remove();
            });
            this.classList.add('bg-primary/10');
            this.insertAdjacentHTML('beforeend', '<svg class="w-4 h-4 ml-auto text-success currency-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>');

            // Close dropdown
            menu.classList.add('hidden');
            chevron.classList.remove('rotate-180');

            // Save to server
            fetch('{{ route("subdomain.set-currency", ["subdomain" => $host->subdomain]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ currency: currency })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update all prices
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error setting currency:', error);
            });
        });
    });
});
</script>
@endpush
@endif

{{-- Language Picker JavaScript --}}
@if($hasMultipleLanguages)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const langPickerBtn = document.getElementById('language-picker-btn');
    const langMenu = document.getElementById('language-menu');
    const langChevron = document.getElementById('language-chevron');
    const languageOptions = document.querySelectorAll('.language-option');
    const langFlagEl = document.getElementById('current-language-flag');
    const langCodeEl = document.getElementById('current-language-code');

    // Toggle dropdown
    langPickerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        langMenu.classList.toggle('hidden');
        langChevron.classList.toggle('rotate-180');
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!langMenu.contains(e.target) && !langPickerBtn.contains(e.target)) {
            langMenu.classList.add('hidden');
            langChevron.classList.remove('rotate-180');
        }
    });

    // Handle language selection
    languageOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            const language = this.dataset.language;
            const flag = this.dataset.flag;

            // Update UI immediately
            langFlagEl.textContent = flag;
            if (langCodeEl) langCodeEl.textContent = language.toUpperCase();

            // Update active state
            languageOptions.forEach(opt => {
                opt.classList.remove('bg-primary/10');
                const checkIcon = opt.querySelector('.language-check');
                if (checkIcon) checkIcon.remove();
            });
            this.classList.add('bg-primary/10');
            this.insertAdjacentHTML('beforeend', '<svg class="w-4 h-4 ml-auto text-success language-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>');

            // Close dropdown
            langMenu.classList.add('hidden');
            langChevron.classList.remove('rotate-180');

            // Save to server
            fetch('{{ route("subdomain.set-language", ["subdomain" => $host->subdomain]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ language: language })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update all translations
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error setting language:', error);
            });
        });
    });
});
</script>
@endpush
@endif
