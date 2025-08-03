<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Services\User\UserService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * @return Factory|View|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function registerView(): Factory|View|Application|\Illuminate\Contracts\Foundation\Application
    {
        return UserService::registerView();
    }

    /**
     * @param RegisterRequest $request
     * @return RedirectResponse|Response
     */
    public function register(RegisterRequest $request): RedirectResponse|Response
    {
        return UserService::register($request);
    }

    /**
     * @param Request $request
     * @return View|Factory|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function getUser(Request $request): View|Factory|Application|\Illuminate\Contracts\Foundation\Application
    {
        return UserService::getUser($request);
    }

    /**
     * @param DeleteUserRequest $request
     * @return RedirectResponse
     */
    public function deleteUser(DeleteUserRequest $request): RedirectResponse
    {
        return UserService::deleteUser($request);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application|RedirectResponse
     */
    public function updateUserView($id): Factory|View|Application|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        return UserService::updateUserView($id);
    }

    /**
     * @param UpdateUserRequest $request
     * @return Response|RedirectResponse
     */
    public function updateUser(UpdateUserRequest $request): Response|RedirectResponse
    {
        return UserService::updateUser($request);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application
     */
    public function updateProfileView(): \Illuminate\Contracts\Foundation\Application|Factory|View|Application
    {
        return UserService::updateProfileView();
    }

    /**
     * @param UpdateProfileRequest $request
     * @return Response|RedirectResponse
     */
    public function updateProfile(UpdateProfileRequest $request): Response|RedirectResponse
    {
        return UserService::updateProfile($request);
    }
}
