<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Display all application settings.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Setting::query()
                ->orderBy('key')
                ->get(['key', 'value'])
                ->pluck('value', 'key'),
        ]);
    }

    /**
     * Update the specified settings in storage.
     */
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully.'
        ]);
    }
}
