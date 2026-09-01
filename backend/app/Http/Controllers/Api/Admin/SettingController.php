<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Update the specified settings in storage.
     */
    public function update(UpdateSettingRequest $request)
    {
        foreach ($request->validated() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json([
            'message' => 'Settings updated successfully.'
        ], 200);
    }
}