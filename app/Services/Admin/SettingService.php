<?php

namespace App\Services\Admin;

use App\Models\Setting;
use App\Services\Admin\AuditLogger;

class SettingService
{
    /**
     * Get all settings grouped by their group name.
     */
    public function getSettings()
    {
        return Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
    }

    /**
     * Update a setting value by ID.
     */
    public function update(int $id, $value): void
    {
        $setting = Setting::findOrFail($id);
        $oldValues = ['value' => $setting->value];

        // Ensure value is cast correctly based on type, for safety (e.g. integer)
        if ($setting->type === 'integer') {
            $value = (int)$value;
        } elseif ($setting->type === 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } elseif ($setting->type === 'float') {
            $value = (float)$value;
        }

        $setting->update(['value' => $value]);

        AuditLogger::log('update_setting', $setting, $oldValues, ['value' => $setting->value]);
    }
}
