@extends('backoffice.layouts.app')

@section('title', 'Edit Admin Member')
@section('page-title', 'Edit Admin Member')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.admin-members.index') }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Admin Members
    </a>

    <form action="{{ route('backoffice.admin-members.update', $adminMember) }}" method="POST">
        @csrf
        @method('PUT')
        @include('backoffice.admin-members._form', ['adminMember' => $adminMember])
    </form>
</div>
@endsection
