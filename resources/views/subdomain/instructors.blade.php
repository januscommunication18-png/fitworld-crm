@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Instructors')

@section('content')
<div class="w-full max-w-4xl space-y-8">

    {{-- Navigation Tabs --}}
    <div class="flex justify-center">
        <div class="tabs tabs-boxed bg-base-100">
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--home] size-4 mr-2"></span>
                Home
            </a>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--calendar] size-4 mr-2"></span>
                Full Schedule
            </a>
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab tab-active">
                <span class="icon-[tabler--users] size-4 mr-2"></span>
                Instructors
            </a>
        </div>
    </div>

    {{-- Instructors Grid --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">
                <span class="icon-[tabler--users] size-5"></span>
                Our Instructors
            </h2>

            @if($instructors->isEmpty())
                <div class="text-center py-8">
                    <span class="icon-[tabler--users-off] size-12 text-base-content/20 mx-auto mb-4"></span>
                    <p class="text-base-content/60">No instructors listed yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    @foreach($instructors as $instructor)
                    <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                       class="flex items-start gap-4 p-4 rounded-lg border border-base-200 hover:border-primary hover:bg-primary/5 transition-all">
                        @if($instructor->photo_url)
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->full_name }}"
                                 class="w-20 h-20 rounded-full object-cover shrink-0">
                        @else
                            <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <span class="text-2xl font-bold text-primary">{{ $instructor->initials }}</span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-lg">{{ $instructor->full_name }}</h3>
                            @if($instructor->title)
                                <p class="text-sm text-base-content/60">{{ $instructor->title }}</p>
                            @endif
                            @if($instructor->specialties)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @php
                                        $specs = is_array($instructor->specialties) ? $instructor->specialties : [$instructor->specialties];
                                    @endphp
                                    @foreach(array_slice($specs, 0, 3) as $specialty)
                                        <span class="badge badge-sm badge-ghost">{{ $specialty }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($instructor->bio)
                                <p class="text-sm text-base-content/70 mt-2 line-clamp-2">{{ $instructor->bio }}</p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
