<?php

namespace App\Services\User;

use App\Jobs\InvitationMailJob;
use Illuminate\Contracts\Foundation\Application;
use App\Http\Resources\User\GetUserResource;
use App\Http\Resources\DataCollection;
use Illuminate\Contracts\View\Factory;
use App\Interfaces\User\UserInterface;
use App\Filters\User\UserEmailFilter;
use Illuminate\Http\RedirectResponse;
use App\Filters\User\UserNameFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Http\Response;
use App\DTOs\User\AddUserDTO;
use App\Models\User;
use App\Helper;

class UserService implements UserInterface
{

    /**
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public static function registerView(): Application|Factory|View|\Illuminate\Foundation\Application
    {
        return view('backend.dashboard.register');
    }

    /**
     * @param $request
     * @return RedirectResponse|Response
     */
    public static function register($request): RedirectResponse|Response
    {
        try {
            DB::beginTransaction();
            $user = User::create((new AddUserDTO($request))->toArray());
            InvitationMailJob::dispatch($user);
            session()->flash('success', "User {$request['name']} is successfully registered.");
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Something went wrong");
            return Helper::errorHandling($request, $e, __FUNCTION__);
        }
    }

    /**
     * @param $request
     * @return View|\Illuminate\Foundation\Application|Factory|Application
     */

    public static function getUser($request): View|\Illuminate\Foundation\Application|Factory|Application
    {

        $users = app(Pipeline::class)
            ->send(User::query())
            ->through([
                UserNameFilter::class,
                UserEmailFilter::class,
            ])
            ->thenReturn()
//            ->with('stream.enrollments', 'subjects')
            ->latest()
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 5);

        $usersCollection = new DataCollection($users);
        $usersCollection->setResourceClass(GetUserResource::class);
        $users = $usersCollection->toArray($request);

        return view('backend.user.get-user', ['users' => $users]);
    }
}
