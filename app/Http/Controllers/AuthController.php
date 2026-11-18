<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Those credentials do not match an AgrifiMarket desk.',
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->isApproved()) {
            return redirect()->route('onboarding');
        }

        return redirect()->intended(route($user->isAdmin() ? 'admin.home' : 'dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'regex:/^0\d{10}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(array_keys(config('marketplace.register_roles')))],
            'account_type' => ['required', Rule::in(array_keys(config('marketplace.account_types')))],
            'state' => ['required', Rule::in(config('marketplace.states'))],
            'city' => ['required', 'string', 'max:80'],
            'rc_number' => ['nullable', 'string', 'max:40'],
        ]);

        if ($data['account_type'] === 'business' && empty($data['business_name'])) {
            return back()->withInput()->withErrors(['business_name' => 'A business desk needs a registered name.']);
        }

        $user = User::create([
            ...$data,
            'status' => 'pending',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding')->with('status', 'Desk created. Upload your documents so an operator can open it.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
