@extends('layouts.app')
@section('title', 'Verify email')
@section('content')
<section class="auth-card verify-card">
    <div class="stat-icon mb-4"><i class="bi bi-envelope-check"></i></div>
    <h2>One last little step.</h2><p class="subtitle">Verify your email to open your workspace.</p>
    <p class="text-secondary">We sent a verification link to <strong class="text-break">{{ auth()->user()->email }}</strong>. Open it in this browser to get started.</p>
    <div class="demo-hint mb-4">In this local demo, emails appear in <code>storage/logs/laravel.log</code>. Copy the complete verification URL from that file.</div>
    <form action="{{ route('verification.send') }}" method="POST" class="js-lock-submit">@csrf<button class="btn btn-primary w-100" type="submit">Resend verification link <i class="bi bi-arrow-right"></i></button></form>
</section>
@endsection
