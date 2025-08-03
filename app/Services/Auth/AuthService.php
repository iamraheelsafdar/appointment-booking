<?php

namespace App\Services\Auth;

use App\Helper;
use App\Jobs\ForgetPasswordJob;
use App\Jobs\PasswordChangedJob;
use Illuminate\Contracts\Foundation\Application;
use App\Interfaces\Auth\AuthInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use App\Models\User;
use Illuminate\Support\Str;

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
        $userStatus = User::where('email', $request->email)->where('status', 1)->exists();
        if (!$userStatus) {
            session()->flash('errors', 'Your account is not active');
            return redirect()->back();
        }

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
     * @return Response|RedirectResponse
     */
    public static function setPassword($request): Response|RedirectResponse
    {
        try {
            $user = tap(User::where('email', $request->email)
                ->where('remember_token', $request->token)
                ->firstOrFail())->update([
                'remember_token' => null,
                'status' => 1,
                'password' => Hash::make($request->password),
                'email_verified_at' => now()
            ]);
            PasswordChangedJob::dispatch($user);
            session()->flash('success', 'Your password set successfully please login');
            return redirect()->route('login');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Something went wrong");
            return Helper::errorHandling($request, $e, __FUNCTION__);
        }
    }

    /**
     * @return Factory|\Illuminate\Foundation\Application|View|Application
     */
    public static function forgetPasswordView(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('backend.auth.forget-password');
    }

    public static function forgetPassword($request): RedirectResponse
    {
        $user = User::where('email', $request->email)->first();
        $user->update([
            'remember_token' => Str::uuid()->toString()
        ]);
        ForgetPasswordJob::dispatch($user);
        session()->flash('success', 'If an account exists with a given email we will send you password set mail');
        return redirect()->route('forgetPassword');
    }
}
