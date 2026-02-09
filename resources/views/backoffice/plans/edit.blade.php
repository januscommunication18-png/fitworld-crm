@extends('backoffice.layouts.app')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Plan')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.plans.index') }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Plans
    </a>

    <form action="{{ route('backoffice.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        @include('backoffice.plans._form', ['plan' => $plan])
    </form>

    {{-- Delete Plan --}}
    <div class="card bg-base-100 border border-error/20">
        <div class="card-body">
            <h3 class="card-title text-error">Danger Zone</h3>
            <p class="text-sm text-base-content/60 mb-4">
                Once you delete a plan, there is no going back. Please be certain.
            </p>
            <form action="{{ route('backoffice.plans.destroy', $plan) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this plan? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error btn-soft">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete Plan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
