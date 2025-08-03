<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * @return Application|View|Factory|\Illuminate\Contracts\Foundation\Application
     */
    public function loginView(): Application|View|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return AuthService::loginView();
    }

    /**
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        return AuthService::login($request);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        return AuthService::logout($request);
    }

    /**
     * @param $email
     * @param $token
     * @return Factory|View|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function setPasswordView($email, $token): Factory|View|Application|\Illuminate\Contracts\Foundation\Application
    {
        return AuthService::setPasswordView($email, $token);
    }

    /**
     * @param SetPasswordRequest $request
     * @return Response|RedirectResponse
     */
    public function setPassword(SetPasswordRequest $request): Response|RedirectResponse
    {
        return AuthService::setPassword($request);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application
     */
    public function forgetPasswordView(): Factory|View|Application|\Illuminate\Contracts\Foundation\Application
    {
        return AuthService::forgetPasswordView();
    }

    /**
     * @param ForgetPasswordRequest $request
     * @return RedirectResponse
     */
    public function forgetPassword(ForgetPasswordRequest $request): RedirectResponse
    {
        return AuthService::forgetPassword($request);
    }
}
