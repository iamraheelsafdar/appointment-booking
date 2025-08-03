<?php

namespace App\Services\User;

use Illuminate\Contracts\Foundation\Application;
use App\Http\Resources\User\GetUserResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\DataCollection;
use Illuminate\Contracts\View\Factory;
use App\Interfaces\User\UserInterface;
use Illuminate\Support\Facades\Hash;
use App\Filters\User\UserEmailFilter;
use Illuminate\Http\RedirectResponse;
use App\Filters\User\UserNameFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pipeline\Pipeline;
use App\DTOs\User\UpdateUserDTO;
use App\Jobs\InvitationMailJob;
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
        return view('backend.user.register');
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
            session()->flash('errors', "Something went wrong");
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
            ->where('id', '!=', auth()->user()->id)
            ->latest()
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10);

        $usersCollection = new DataCollection($users);
        $usersCollection->setResourceClass(GetUserResource::class);
        $users = $usersCollection->toArray($request);

        return view('backend.user.get-user', ['users' => $users]);
    }

    /**
     * @param $request
     * @return RedirectResponse
     */
    public static function deleteUser($request): RedirectResponse
    {
        User::find($request->id)->delete();
        session()->flash('success', "User deleted successfully.");
        return redirect()->back();
    }

    /**
     * @param $id
     * @return Factory|\Illuminate\Foundation\Application|View|Application|RedirectResponse
     */
    public static function updateUserView($id): Factory|\Illuminate\Foundation\Application|View|Application|RedirectResponse
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('errors', "User not found.");
            return redirect()->back();
        }
        return view('backend.user.update-user', ['user' => $user]);
    }

    /**
     * @param $request
     * @return Response|RedirectResponse
     */
    public static function updateUser($request): Response|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = User::find($request->id);
            $user->update((new UpdateUserDTO($request, $user))->toArray());
            session()->flash('success', "User Updated successfully.");
            DB::commit();
            return redirect()->route('getUser');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('errors', "Something went wrong");
            return Helper::errorHandling($request, $e, __FUNCTION__);
        }
    }

    /**
     * @return Factory|\Illuminate\Foundation\Application|View|Application
     */
    public static function updateProfileView(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('backend.user.update-profile', ['user' => auth()->user()]);
    }

    /**
     * @param $request
     * @return Response|RedirectResponse
     */
    public static function updateProfile($request): Response|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $profileSetting = User::where('id', auth()->user()->id)->first();
            $profileSetting->update([
                'name' => $request->name ?: $profileSetting->name,
                'email' => $request->email ?: $profileSetting->email,
                'phone' => $request->phone ?: $profileSetting->phone
            ]);
            if ($profileSetting->profile_image && $request->hasFile('profile_image')) {
                Storage::disk('public')->delete($profileSetting->profile_image);
            }
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $path = Storage::disk('public')->put('profile_settings/', $file);
                $profileSetting->update(['profile_image' => $path]);
            }

            if ($request->filled('new_password')) {
                if (Hash::check($request->old_password, $profileSetting->password)) {
                    $profileSetting->update([
                        'password' => Hash::make($request->new_password)
                    ]);
                } else {
                    DB::commit();
                    return redirect()->back()->with('errors', 'Old password is incorrect');
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Profile Updated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('errors', "Something went wrong");
            return Helper::errorHandling($request, $e, __FUNCTION__);
        }
    }
}
