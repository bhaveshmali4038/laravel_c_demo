@extends('layouts.app')
@section('title', 'Account security')
@section('content')
<a class="back-link" href="{{ route('products.index') }}"><i class="bi bi-arrow-left"></i> Back to inventory</a>
<div class="page-heading"><div><div class="eyebrow">A LITTLE PEACE OF MIND</div><h1>Account security</h1><p class="text-secondary">Keep your workspace protected with a strong password.</p></div></div>
<section class="panel" style="max-width:650px"><div class="section-body">
    <h2 class="section-heading">Change password</h2>
    <form action="{{ route('account.password.update') }}" method="POST" class="js-lock-submit">@csrf @method('PUT')
        <x-input name="current_password" label="Current password" type="password" required autocomplete="current-password" />
        <x-input name="password" label="New password" type="password" required minlength="10" autocomplete="new-password" />
        <x-input name="password_confirmation" label="Confirm new password" type="password" required minlength="10" autocomplete="new-password" />
        <p class="password-hint mb-4">At least 10 characters with uppercase and lowercase letters, a number, and a symbol.</p>
        <button class="btn btn-primary" type="submit"><i class="bi bi-shield-check"></i> Update password</button>
    </form>
</div></section>
@endsection
