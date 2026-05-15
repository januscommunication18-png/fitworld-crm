@extends('layouts.dashboard')

@section('content')
<div class="min-w-0">
    @yield('settings-content')
</div>
@endsection

@push('scripts')
<script>
// Close all details dropdowns when clicking outside
document.addEventListener('click', function(e) {
    var allDetails = document.querySelectorAll('details.dropdown[open]');
    allDetails.forEach(function(details) {
        if (!details.contains(e.target)) {
            details.removeAttribute('open');
        }
    });
});
</script>
@endpush
