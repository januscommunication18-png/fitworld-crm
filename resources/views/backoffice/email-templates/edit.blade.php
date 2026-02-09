@extends('backoffice.layouts.app')

@section('title', 'Edit Email Template')
@section('page-title', 'Edit Email Template')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.email-templates.index', ['tab' => $emailTemplate->host_id ? 'client' : 'system', 'host_id' => $emailTemplate->host_id]) }}"
       class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Email Templates
    </a>

    <form action="{{ route('backoffice.email-templates.update', $emailTemplate) }}" method="POST">
        @csrf
        @method('PUT')
        @include('backoffice.email-templates._form', ['emailTemplate' => $emailTemplate])
    </form>
</div>
@endsection
