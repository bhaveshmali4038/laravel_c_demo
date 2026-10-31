<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Products') · Stockroom</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@guest auth-body @endguest">
@auth
<aside class="sidebar d-none d-lg-flex">
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark"><i class="bi bi-box-seam"></i></span>stockroom<span class="brand-dot">.</span></a>
    <div class="workspace-label">YOUR WORKSPACE</div>
    <a class="side-link {{ request()->routeIs('products.*') ? 'selected' : '' }}" href="{{ route('products.index') }}"><i class="bi bi-grid-1x2"></i> Product inventory <span class="ms-auto small">↗</span></a>
    <a class="side-link {{ request()->routeIs('account.*') ? 'selected' : '' }}" href="{{ route('account.password.edit') }}"><i class="bi bi-shield-check"></i> Account security</a>
    <div class="sidebar-note"><span class="note-dot"></span> A little more organized.<p>Your products, pricing, and inventory. All in one place.</p></div>
    <div class="sidebar-user"><div class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div><div class="user-details"><strong>{{ auth()->user()->name }}</strong><span>Personal workspace</span></div><form action="{{ route('logout') }}" method="POST">@csrf<button class="icon-btn" title="Log out" aria-label="Log out"><i class="bi bi-box-arrow-right"></i></button></form></div>
</aside>
<div class="app-shell">
    <header class="topbar">
        <a class="brand d-lg-none" href="{{ url('/') }}"><span class="brand-mark"><i class="bi bi-box-seam"></i></span>stockroom.</a>
        <div class="d-none d-lg-flex breadcrumb-line">Workspace <i class="bi bi-chevron-right"></i> <span>@yield('title', 'Products')</span></div>
        <div class="d-flex align-items-center gap-3"><span class="environment-label"><span></span> Local demo</span><a class="topbar-security" href="{{ route('account.password.edit') }}" aria-label="Account security"><i class="bi bi-shield-check"></i></a><form class="d-lg-none" action="{{ route('logout') }}" method="POST">@csrf<button class="icon-btn" aria-label="Log out"><i class="bi bi-box-arrow-right"></i></button></form></div>
    </header>
@else
<header class="auth-header"><a class="brand" href="{{ url('/') }}"><span class="brand-mark"><i class="bi bi-box-seam"></i></span>stockroom<span class="brand-dot">.</span></a><span class="environment-label"><span></span> Laravel CRUD demo</span></header>
@endauth
<main class="main-content {{ auth()->guest() ? 'auth-main' : '' }}">
    @if (session('success'))<div class="alert alert-success d-flex gap-2" role="alert"><i class="bi bi-check-circle"></i><div>{{ session('success') }}</div></div>@endif
    @if (session('status'))<div class="alert alert-info" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger" role="alert"><strong>A few details need your attention.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div id="client-message" class="alert d-none" role="alert" tabindex="-1"></div>
    @yield('content')
    <footer class="page-footer"><span>Made to keep things simple.</span><span>Laravel 12 <span class="mx-2">/</span> Blade + Bootstrap</span></footer>
</main>
@auth</div>@endauth
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
