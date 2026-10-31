@extends('layouts.app')
@section('title', 'Forgot password')
@section('content')
<div class="auth-grid">
    @include('auth.partials.intro')
    <section class="auth-card">
        <h2>Let's get you back in.</h2><p class="subtitle">Enter your email to request a password-reset link.</p>
        <form action="{{ route('password.email') }}" method="POST" class="js-lock-submit">@csrf
            <x-input name="email" label="Email address" type="email" required autocomplete="email" />
            <button class="btn btn-primary w-100" type="submit">Send reset link <i class="bi bi-arrow-right"></i></button>
        </form>
        <div class="demo-hint">Local email is written to <code>storage/logs/laravel.log</code>. Open the reset link from that file.</div>
        <p class="auth-switch"><a href="{{ route('login') }}">Back to login</a></p>
    </section>
</div>
@endsection
