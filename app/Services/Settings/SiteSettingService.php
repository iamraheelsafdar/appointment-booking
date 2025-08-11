<?php

namespace App\Services\Settings;

use App\Interfaces\Settings\SettingsInterface;
use App\Models\SiteSettings;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingService implements SettingsInterface
{

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public static function updateSettingView(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('backend.settings.setting');
    }

    /**
     * @param $request
     * @return RedirectResponse
     */
    public static function updateSetting($request): RedirectResponse
    {
        if ($request->buffer_minutes != $request->slot_difference){
            return redirect()->back()->with('errors', 'Buffer minutes and slot difference should be same.');
        }
        $siteSettings = SiteSettings::firstOrCreate(['id' => 1]);
        $siteSettings->update([
            'title' => $request->title,
            'copyright' => $request->copyright,
            'buffer_minutes' => $request->buffer_minutes,
            'slot_difference' => $request->slot_difference,
        ]);
        if ($siteSettings->logo && $request->hasFile('logo')) {
            Storage::disk('public')->delete($siteSettings->logo);
        }
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = Storage::disk('public')->put('site_settings/', $file);
            $siteSettings->update(['logo' => $path]);
        }
        return redirect()->back()->with('success', 'Setting Updated Successfully');
    }
}
