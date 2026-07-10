<?php

namespace App\Services\AiModelsHub;

use App\Models\AIApiKey;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EncryptedApiKeyStorage
{
    /**
     * Store an encrypted API key
     */
    public function storeKey($providerId, $key, $name = null)
    {
        $encryptedKey = Crypt::encryptString($key);

        $hasDefault = AIApiKey::where('provider_id', $providerId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->exists();

        $apiKey = AIApiKey::create([
            'id' => Str::uuid(),
            'provider_id' => $providerId,
            'key_hash' => $encryptedKey,
            'name' => $name ?? "API Key for Provider {$providerId}",
            'is_active' => true,
            'is_default' => ! $hasDefault,
        ]);

        return $apiKey;
    }

    /**
     * Get decrypted API key by provider ID
     */
    public function getDecryptedKey($providerId)
    {
        $apiKey = AIApiKey::where('provider_id', $providerId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if (! $apiKey) {
            return null;
        }

        try {
            return Crypt::decryptString($apiKey->key_hash);
        } catch (\Exception $e) {
            Log::error("Failed to decrypt API key for provider {$providerId}: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Check if API key exists for provider
     */
    public function hasKey($providerId)
    {
        return AIApiKey::where('provider_id', $providerId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Update API key
     */
    public function updateKey($providerId, $key, $name = null)
    {
        $encryptedKey = Crypt::encryptString($key);

        $apiKey = AIApiKey::where('provider_id', $providerId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if ($apiKey) {
            $apiKey->update([
                'key_hash' => $encryptedKey,
                'name' => $name ?? $apiKey->name,
                'updated_at' => now(),
            ]);
        } else {
            $apiKey = $this->storeKey($providerId, $key, $name);
        }

        return $apiKey;
    }

    /**
     * Deactivate API key
     */
    public function deactivateKey($providerId)
    {
        $apiKey = AIApiKey::where('provider_id', $providerId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if ($apiKey) {
            $apiKey->update(['is_active' => false]);

            return true;
        }

        return false;
    }
}
