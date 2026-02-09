@extends('backoffice.layouts.app')

@section('title', 'Invoice')
@section('page-title', 'Invoice')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        @include('backoffice.components.coming-soon', [
            'title' => 'Invoice Management',
            'description' => 'View and manage invoices for FitCRM subscriptions. Track payments and generate billing reports.',
            'icon' => 'tabler--file-invoice'
        ])
    </div>
</div>
@endsection
