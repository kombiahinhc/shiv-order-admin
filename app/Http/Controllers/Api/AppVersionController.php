<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    public function check(): JsonResponse
    {
        $latest = AppVersion::latest('build_number')->first();

        if (!$latest) {
            return response()->json([
                'has_update' => false,
            ]);
        }

        return response()->json([
            'has_update' => true,
            'version' => $latest->version,
            'build_number' => $latest->build_number,
            'release_notes' => $latest->release_notes,
            'is_force_update' => $latest->is_force_update,
            'download_url' => $latest->download_url,
        ]);
    }
}
