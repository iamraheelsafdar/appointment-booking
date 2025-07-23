<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Foundation\Application;
use App\Interfaces\Auth\AuthInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use App\Models\User;

class AuthService implements AuthInterface
{
    /**
     * @return Factory|\Illuminate\Foundation\Application|View|Application
     */
    public static function loginView(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('backend.auth.login');
    }

    /**
     * @param $request
     * @return RedirectResponse
     */
    public static function login($request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $rememberMe = $request->boolean('remember_me');
        $login = Auth::attempt($credentials, $rememberMe);
        if (!$login) {
            session()->flash('errors', 'Invalid credentials');
            return redirect()->back();
        }
        return redirect()->route('dashboard');
    }

    /**
     * @param $request
     * @return RedirectResponse
     */
    public static function logout($request): RedirectResponse
    {
        Auth::logout();
        session()->flash('success', 'You are successfully logout');
        return redirect()->route('login');
    }

    /**
     * @param $email
     * @param $token
     * @return Factory|\Illuminate\Foundation\Application|View|Application
     */
    public static function setPasswordView($email, $token): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $user = User::where('email', $email)->where('remember_token', $token)->exists();
        if (!$user) {
            return view('link-expire');
        }
        return view('backend.auth.set-password', ['email' => $email, 'token' => $token]);
    }

    /**
     * @param $request
     * @return RedirectResponse
     */
    public static function setPassword($request): RedirectResponse
    {
        User::where('email', $request->email)->where('remember_token', $request->token)
            ->update([
                'remember_token' => null,
                'status' => 1,
                'password' => Hash::make($request->password)
            ]);
        session()->flash('success', 'Your password set successfully please login');
        return redirect()->route('login');
    }
}
