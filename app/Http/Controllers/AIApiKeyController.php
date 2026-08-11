<?php

namespace App\Http\Controllers;

use App\Models\AIApiKey;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIApiKeyController extends Controller
{
    protected $keyStorage;

    public function __construct(EncryptedApiKeyStorage $keyStorage)
    {
        $this->keyStorage = $keyStorage;
    }

    public function indexForProvider($id)
    {
        $keys = AIApiKey::where('provider_id', $id)
            ->select('id', 'name', 'status', 'is_active', 'is_default', 'last_used_at', 'created_at', DB::raw("CONCAT('sk-...****', SUBSTRING(key_hash, -4)) as masked_key"))
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $keys]);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required_without:key_value|nullable|string',
            'key_value' => 'required_without:api_key|nullable|string',
        ]);

        $keyVal = $request->api_key ?: $request->key_value;

        try {
            $isFirst = AIApiKey::where('provider_id', $id)->count() === 0;

            $keyId = $this->keyStorage->storeKey($id, $keyVal, $request->name);

            $updateData = [];
            if ($isFirst || $request->boolean('is_default')) {
                AIApiKey::where('provider_id', $id)->update(['is_default' => false]);
                $updateData['is_default'] = true;
            }
            if ($request->filled('priority')) {
                $updateData['priority'] = (int) $request->priority;
            }
            if ($request->filled('expires_at')) {
                $updateData['expires_at'] = $request->expires_at;
            }

            if (! empty($updateData)) {
                AIApiKey::where('id', $keyId)->update($updateData);
            }

            return response()->json(['success' => true, 'message' => 'API Key added successfully']);
        } catch (\Exception $e) {
            Log::error('Error adding API key: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to add API key: '.$e->getMessage()], 500);
        }
    }

    public function destroy($keyId)
    {
        try {
            $key = AIApiKey::find($keyId);
            if (! $key) {
                return response()->json(['success' => false], 404);
            }

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
            $key = AIApiKey::find($keyId);
            if (! $key) {
                return response()->json(['success' => false], 404);
            }

            // Unset others
            AIApiKey::where('provider_id', $key->provider_id)->update(['is_default' => false]);
            // Set this
            $key->update(['is_default' => true]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
