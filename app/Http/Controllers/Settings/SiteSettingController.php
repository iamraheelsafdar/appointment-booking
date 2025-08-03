<?php

namespace App\Http\Controllers\Settings;

use App\Http\Requests\Settings\SiteSettingRequest;
use App\Services\Settings\SiteSettingService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SiteSettingController extends Controller
{

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function updateSettingView(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return SiteSettingService::updateSettingView();
    }

    /**
     * @param SiteSettingRequest $request
     * @return RedirectResponse
     */
    public function updateSetting(SiteSettingRequest $request): RedirectResponse
    {
        return SiteSettingService::updateSetting($request);
    }
}
