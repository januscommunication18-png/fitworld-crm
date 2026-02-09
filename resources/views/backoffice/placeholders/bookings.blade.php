@extends('backoffice.layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        @include('backoffice.components.coming-soon', [
            'title' => 'Bookings Overview',
            'description' => 'View and manage bookings across all FitCRM clients. Monitor booking trends and cancellation rates.',
            'icon' => 'tabler--calendar-check'
        ])
    </div>
</div>
@endsection
