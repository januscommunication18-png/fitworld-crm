@extends('backoffice.layouts.app')

@section('title', 'Members')
@section('page-title', 'Members')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        @include('backoffice.components.coming-soon', [
            'title' => 'Members Overview',
            'description' => 'View all members across FitCRM clients. Monitor membership growth and retention metrics.',
            'icon' => 'tabler--users-group'
        ])
    </div>
</div>
@endsection
