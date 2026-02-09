@extends('backoffice.layouts.app')

@section('title', 'Schedule')
@section('page-title', 'Schedule')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        @include('backoffice.components.coming-soon', [
            'title' => 'Schedule Management',
            'description' => 'View consolidated schedules across all FitCRM clients. Monitor class occupancy and scheduling patterns.',
            'icon' => 'tabler--calendar'
        ])
    </div>
</div>
@endsection
