@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Coming Soon')

@section('content')

{{-- Navigation Bar - 75px height --}}
<nav class="bg-base-100 border-b border-base-200" style="height: 75px;">
    <div class="container-fixed h-full">
        <div class="flex items-center justify-between h-full">
            {{-- Left: Logo --}}
            <div class="flex items-center">
                @if($host->logo_url)
                    <div class="flex items-center">
                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-auto max-w-[180px] object-contain">
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-lg font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                        </div>
                        <span class="font-bold text-lg hidden sm:inline">{{ $host->studio_name }}</span>
                    </div>
                @endif
            </div>

            {{-- Right: Social Icons --}}
            <div class="flex items-center gap-3">
                @if(($host->show_social_links ?? true) && $host->social_links && count(array_filter((array)$host->social_links)))
                <div class="flex items-center gap-1">
                    @foreach(['instagram' => 'brand-instagram', 'facebook' => 'brand-facebook', 'tiktok' => 'brand-tiktok', 'twitter' => 'brand-x', 'website' => 'world'] as $key => $icon)
                        @if(!empty($host->social_links[$key]))
                        <a href="{{ $host->social_links[$key] }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-full bg-base-200 hover:bg-primary hover:text-white flex items-center justify-center transition-colors"
                           title="{{ ucfirst($key) }}">
                            <span class="icon-[tabler--{{ $icon }}] size-5"></span>
                        </a>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</nav>

<div class="min-h-[calc(100vh-75px-80px)] flex items-center justify-center px-4">
    <div class="w-full max-w-md text-center space-y-8 animate-fade-in">
        {{-- Coming Soon Icon --}}
        <div class="w-24 h-24 rounded-full bg-primary/10 flex items-center justify-center mx-auto">
            <span class="icon-[tabler--clock] size-12 text-primary"></span>
        </div>

        {{-- Studio Name --}}
        <h1 class="text-2xl md:text-3xl font-bold text-base-content">{{ $host->studio_name }}</h1>

        {{-- Coming Soon Badge --}}
        <div class="flex justify-center">
            <div class="inline-flex items-center gap-3 px-6 py-3 bg-primary/10 rounded-full">
                <span class="icon-[tabler--clock] size-6 text-primary"></span>
                <span class="font-semibold text-primary">Coming Soon</span>
            </div>
        </div>

        {{-- Message --}}
        <p class="text-base-content/60 max-w-sm mx-auto">
            Our booking page is currently being set up. Please check back soon to book classes and services!
        </p>

        {{-- Contact Info --}}
        @if($host->studio_email || $host->phone)
        <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body p-5">
                <p class="text-sm text-base-content/60 mb-4">In the meantime, you can reach us at:</p>
                <div class="flex flex-col gap-2">
                    @if($host->studio_email)
                        <a href="mailto:{{ $host->studio_email }}"
                           class="flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-base-200 hover:bg-base-300 transition-colors">
                            <span class="icon-[tabler--mail] size-5 text-primary"></span>
                            <span class="text-sm font-medium">{{ $host->studio_email }}</span>
                        </a>
                    @endif
                    @if($host->phone)
                        <a href="tel:{{ $host->phone }}"
                           class="flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-base-200 hover:bg-base-300 transition-colors">
                            <span class="icon-[tabler--phone] size-5 text-primary"></span>
                            <span class="text-sm font-medium">{{ $host->phone }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
