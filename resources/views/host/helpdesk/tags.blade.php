@extends('layouts.dashboard')

@section('title', 'Help Desk Tags')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('helpdesk.index') }}"><span class="icon-[tabler--help] me-1 size-4"></span> Help Desk</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Tags</li>
    </ol>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--arrow-left] size-4"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Manage Tags</h1>
            <p class="text-base-content/60 mt-1">Create and manage tags for organizing tickets.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-soft alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Create Tag Form --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Create New Tag</h2>
            <form action="{{ route('helpdesk.tags.store') }}" method="POST" class="flex gap-4 items-end">
                @csrf
                <div class="flex-1">
                    <label class="label-text" for="name">Tag Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="input w-full @error('name') input-error @enderror"
                           placeholder="e.g., Urgent, Follow-up, New Member" required>
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="w-24">
                    <label class="label-text" for="color">Color</label>
                    <input type="color" id="color" name="color" value="{{ old('color', '#6366f1') }}"
                           class="w-full h-10 rounded-lg border border-base-content/20 cursor-pointer">
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Tag
                </button>
            </form>

            {{-- Color Presets --}}
            <div class="mt-4">
                <label class="label-text mb-2 block">Quick Colors</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($colors as $color)
                        <button type="button"
                                onclick="document.getElementById('color').value = '{{ $color }}'"
                                class="w-8 h-8 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform"
                                style="background-color: {{ $color }}"
                                title="{{ $color }}">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Existing Tags --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Existing Tags</h2>
            @if($tags->isEmpty())
                <div class="text-center py-8">
                    <span class="icon-[tabler--tags] size-12 text-base-content/20"></span>
                    <p class="text-base-content/60 mt-2">No tags created yet</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($tags as $tag)
                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $tag->color }}"></div>
                                <span class="font-medium">{{ $tag->name }}</span>
                                <span class="text-xs text-base-content/60">{{ $tag->tickets_count }} ticket(s)</span>
                            </div>
                            <form action="{{ route('helpdesk.tags.destroy', $tag) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this tag? It will be removed from all tickets.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-error">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
