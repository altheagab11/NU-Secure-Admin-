{{-- Backwards-compatible alias: mobile topbar is now the shared guard topbar --}}
@include('guard.partials.guard-topbar', ['title' => $title ?? ''])
