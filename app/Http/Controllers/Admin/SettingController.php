<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Services\Admin\SettingService;
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

    public function update(UpdateSettingRequest $request)
    {
        $this->settingService->update($request->validated()['id'], $request->validated()['value']);
        
        return back()->with('success', 'Setting updated successfully.');
    }
}
