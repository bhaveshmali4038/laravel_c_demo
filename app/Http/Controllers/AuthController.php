<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }
    }
    private function passwordRules(): array
    {
        return ['required', 'string', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()->symbols()];
    }

    public function register(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ]);
        $user = User::create([
            'name' => $data['name'], 'email' => Str::lower($data['email']), 'password' => $data['password'],
        ]);
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('verification.notice');
    }

    public function login(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate([
            'email' => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'string'], 'remember' => ['sometimes', 'boolean'],
        ]);
        $key = 'login:'.hash('sha256', Str::lower($data['email']).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages(['email' => "Too many attempts. Try again in {$seconds} seconds."]);
        }
        if (!Auth::attempt(['email' => Str::lower($data['email']), 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'The supplied credentials are incorrect.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        return redirect()->intended(route('products.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate(['email' => ['required', 'string', 'email:rfc']]);
        PasswordBroker::sendResetLink(['email' => Str::lower($data['email'])]);
        return back()->with('status', 'If that email is registered, a password-reset link will be sent. Please wait before requesting another.');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate([
            'token' => ['required', 'string'], 'email' => ['required', 'string', 'email:rfc'],
            'password' => $this->passwordRules(),
        ]);
        $data['email'] = Str::lower($data['email']);
        $status = PasswordBroker::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        if ($status !== PasswordBroker::PasswordReset) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }
        return redirect()->route('login')->with('success', 'Password reset successfully. Please log in.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => [...$this->passwordRules(), 'different:current_password'],
        ]);
        $request->user()->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
        $request->session()->regenerate();
        return back()->with('success', 'Your password has been changed.');
    }
}
