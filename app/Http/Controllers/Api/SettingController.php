<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key');

        return $this->successResponse($settings, 'Settings retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }

        AuditLogger::log('update', 'settings', 0);

        return $this->successResponse(Setting::all()->pluck('value', 'key'), 'Settings updated successfully');
    }
}
