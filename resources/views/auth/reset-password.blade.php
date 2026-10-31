@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
<div class="auth-grid">
    @include('auth.partials.intro')
    <section class="auth-card">
        <h2>A new password.</h2><p class="subtitle">Choose something secure and unique.</p>
        <form action="{{ route('password.update') }}" method="POST" class="js-lock-submit">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-input name="email" label="Email address" type="email" :value="$email" required autocomplete="email" />
            <x-input name="password" label="New password" type="password" required minlength="10" autocomplete="new-password" />
            <x-input name="password_confirmation" label="Confirm new password" type="password" required minlength="10" autocomplete="new-password" />
            <p class="password-hint">At least 10 characters with uppercase and lowercase letters, a number, and a symbol.</p>
            <button class="btn btn-primary w-100" type="submit">Reset password <i class="bi bi-arrow-right"></i></button>
        </form>
    </section>
</div>
@endsection
