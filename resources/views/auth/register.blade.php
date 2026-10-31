@extends('layouts.app')
@section('title', 'Create account')
@section('content')
<div class="auth-grid">
    @include('auth.partials.intro')
    <section class="auth-card">
        <h2>A fresh start.</h2><p class="subtitle">Create your personal product workspace.</p>
        <form action="{{ route('register.store') }}" method="POST" class="js-lock-submit">
            @csrf
            <x-input name="name" label="Full name" required minlength="2" maxlength="100" autocomplete="name" placeholder="Your name" />
            <x-input name="email" label="Email address" type="email" required maxlength="255" autocomplete="email" placeholder="you@example.com" />
            <x-input name="password" label="Password" type="password" required minlength="10" autocomplete="new-password" />
            <x-input name="password_confirmation" label="Confirm password" type="password" required minlength="10" autocomplete="new-password" />
            <p class="password-hint">At least 10 characters with uppercase and lowercase letters, a number, and a symbol.</p>
            <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="terms" id="terms" value="1" required @checked(old('terms'))><label class="form-check-label" for="terms">I understand this is a learning demo.</label></div>
            <button class="btn btn-primary w-100" type="submit">Create my workspace <i class="bi bi-arrow-right"></i></button>
        </form>
        <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </section>
</div>
@endsection
