<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AIApiKeyController extends Controller
{
    protected $keyStorage;

    public function __construct(\App\Services\AiModelsHub\EncryptedApiKeyStorage $keyStorage)
    {
        $this->keyStorage = $keyStorage;
    }

    public function indexForProvider($id)
    {
        $keys = \App\Models\AIApiKey::where('provider_id', $id)
            ->select('id', 'name', 'status', 'is_active', 'is_default', 'last_used_at', 'created_at', \Illuminate\Support\Facades\DB::raw("CONCAT('sk-...****', SUBSTRING(key_hash, -4)) as masked_key"))
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $keys]);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
        ]);

        try {
            // Check if it's the first key, if so make it default
            $isFirst = \App\Models\AIApiKey::where('provider_id', $id)->count() === 0;

            $keyId = $this->keyStorage->storeKey($id, $request->api_key, $request->name);

            if ($isFirst) {
                \App\Models\AIApiKey::where('id', $keyId)->update(['is_default' => true]);
            }

            return response()->json(['success' => true, 'message' => 'API Key added successfully']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error adding API key: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add API key'], 500);
        }
    }

    public function destroy($keyId)
    {
        try {
            $key = \App\Models\AIApiKey::find($keyId);
            if (!$key) return response()->json(['success' => false], 404);

            // If it's the default, we might need to set another one as default, but let's just delete for now
            $key->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function setDefault($keyId)
    {
        try {
            $key = \App\Models\AIApiKey::find($keyId);
            if (!$key) return response()->json(['success' => false], 404);

            // Unset others
            \App\Models\AIApiKey::where('provider_id', $key->provider_id)->update(['is_default' => false]);
            // Set this
            $key->update(['is_default' => true]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
