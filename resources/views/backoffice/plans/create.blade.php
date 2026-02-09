@extends('backoffice.layouts.app')

@section('title', 'Create Plan')
@section('page-title', 'Create Plan')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.plans.index') }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Plans
    </a>

    <form action="{{ route('backoffice.plans.store') }}" method="POST">
        @csrf
        @include('backoffice.plans._form')
    </form>
</div>
@endsection
