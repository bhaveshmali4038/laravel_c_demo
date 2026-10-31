@extends('layouts.app')
@section('title', 'Welcome back')
@section('content')
<div class="auth-grid">
    @include('auth.partials.intro')
    <section class="auth-card">
        <div class="section-kicker">YOUR WORKSPACE IS READY</div>
        <h2>Welcome back.</h2><p class="subtitle">A little organization goes a long way.</p>
        <form action="{{ route('login.store') }}" method="POST" class="js-lock-submit">
            @csrf
            <x-input name="email" label="Email address" type="email" required autocomplete="email" placeholder="you@example.com" />
            <x-input name="password" label="Password" type="password" required autocomplete="current-password" placeholder="Enter your password" />
            <div class="d-flex justify-content-between align-items-center mb-4"><div class="form-check"><input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))><label class="form-check-label" for="remember">Remember me</label></div><a class="small" href="{{ route('password.request') }}">Forgot password?</a></div>
            <button class="btn btn-primary w-100" type="submit">Log in to Stockroom <i class="bi bi-arrow-right"></i></button>
        </form>
        <p class="auth-switch">New around here? <a href="{{ route('register') }}">Create an account</a></p>
        <div class="demo-hint"><i class="bi bi-info-circle me-1"></i> Try the seeded workspace:<br><code>demo@example.com</code> / <code>DemoPass!2026</code></div>
    </section>
</div>
@endsection
