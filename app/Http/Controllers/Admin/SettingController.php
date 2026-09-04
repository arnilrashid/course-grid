<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Admin\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function __construct(private SettingService $settingService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $this->settingService->getSettings(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:settings,id',
            'value' => 'required',
        ]);

        $setting = Setting::findOrFail($request->input('id'));
        $this->settingService->update($setting, $request->input('value'));
        
        return back()->with('success', 'Setting updated successfully.');
    }
}
