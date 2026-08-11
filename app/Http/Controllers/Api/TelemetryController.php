<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TelemetryController extends Controller
{
    public function uploadScreenshot(Request $request, $deviceId)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->storeAs('screenshots/'.$deviceId, time().'.webp', 'public');

            return response()->json(['url' => asset('storage/'.$path)], 200);
        }

        return response()->json(['error' => 'No image found'], 400);
    }

    public function getLatestScreenshot($deviceId)
    {
        $dir = 'screenshots/'.$deviceId;

        if (! Storage::disk('public')->exists($dir)) {
            return redirect('https://via.placeholder.com/400x250.webp?text=No+Image');
        }

        $files = Storage::disk('public')->files($dir);

        if (empty($files)) {
            return redirect('https://via.placeholder.com/400x250.webp?text=No+Image');
        }

        // Sort files to get the latest one, since they are named with timestamps
        sort($files);
        $latest = end($files);

        // Return a redirect to the public URL of the asset
        return redirect(asset('storage/'.$latest));
    }

    public function getAllScreenshots($deviceId)
    {
        $dir = 'screenshots/'.$deviceId;

        if (! Storage::disk('public')->exists($dir)) {
            return response()->json(['screenshots' => []], 200);
        }

        $files = Storage::disk('public')->files($dir);

        // Sort files to get the newest first
        rsort($files);

        $urls = array_map(function ($file) {
            return asset('storage/'.$file);
        }, $files);

        return response()->json(['screenshots' => $urls], 200);
    }
}
