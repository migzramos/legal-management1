<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Client Portal') — LegalCase</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('client.partials.styles')
</head>
<body>
<div class="app-layout">
    @include('client.partials.sidebar')
    <main class="main-area">
        <header class="topbar">
            <div>
                <div class="topbar-title">LegalCase Portal</div>
                <div class="topbar-subtitle">@yield('title', 'Client Dashboard')</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('client.profile') }}" class="topbar-avatar" title="{{ auth()->user()->name ?? 'Profile' }}">
                    {{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}
                </a>
            </div>
        </header>
        <div class="page-content">
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
