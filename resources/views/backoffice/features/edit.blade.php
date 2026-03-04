@extends('backoffice.layouts.app')

@section('title', 'Edit Feature')
@section('page-title', 'Edit Feature')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.features.index') }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Features
    </a>

    <form action="{{ route('backoffice.features.update', $feature) }}" method="POST">
        @csrf
        @method('PUT')
        @include('backoffice.features._form')
    </form>
</div>
@endsection
