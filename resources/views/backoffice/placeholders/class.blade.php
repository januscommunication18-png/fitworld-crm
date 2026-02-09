@extends('backoffice.layouts.app')

@section('title', 'Class')
@section('page-title', 'Class')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        @include('backoffice.components.coming-soon', [
            'title' => 'Class Management',
            'description' => 'Manage all classes across your FitCRM clients. View class schedules, attendance, and performance metrics.',
            'icon' => 'tabler--yoga'
        ])
    </div>
</div>
@endsection
