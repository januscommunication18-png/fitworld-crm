@extends('backoffice.layouts.app')

@section('title', 'Create Email Template')
@section('page-title', 'Create Email Template')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.email-templates.index') }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Email Templates
    </a>

    <form action="{{ route('backoffice.email-templates.store') }}" method="POST">
        @csrf
        @include('backoffice.email-templates._form', ['hostId' => $hostId])
    </form>
</div>
@endsection
