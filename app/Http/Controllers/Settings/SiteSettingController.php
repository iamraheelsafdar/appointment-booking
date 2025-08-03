<?php

namespace App\Http\Controllers\Settings;

use App\Http\Requests\Settings\SiteSettingRequest;
use App\Services\Settings\SiteSettingService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
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

    public function updateSetting(SiteSettingRequest $request)
    {
        return SiteSettingService::updateSetting($request);
    }
}
