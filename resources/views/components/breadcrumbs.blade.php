@hasSection('breadcrumbs')
<div class="breadcrumbs mb-4">
    <ol>
        <li>
            <a href="{{ url('/dashboard') }}">
                <span class="icon-[tabler--home] size-4"></span> Dashboard
            </a>
        </li>
        @yield('breadcrumbs')
    </ol>
</div>
@endif
