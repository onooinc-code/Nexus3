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
     * Get decrypted API key by provider ID, employing round-robin rotation and cooldown filtering.
     */
    public function getDecryptedKey($providerId, ?string &$usedKeyId = null)
    {
        // Auto-release keys whose cooldown duration has expired
        AIApiKey::where('provider_id', $providerId)
            ->where('status', 'cooldown')
            ->where('cooldown_until', '<=', now())
            ->update(['status' => 'active', 'cooldown_until' => null]);

        // Select an available key that is not expired or in cooldown, using LRU rotation
        $apiKey = AIApiKey::where('provider_id', $providerId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['expired', 'cooldown']);
            })
            ->where(function ($query) {
                $query->whereNull('cooldown_until')
                    ->orWhere('cooldown_until', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('last_used_at', 'asc')
            ->orderByDesc('is_default')
            ->first();

        if (! $apiKey) {
            return null;
        }

        $apiKey->update([
            'last_used_at' => now(),
            'last_rotated_at' => now(),
        ]);

        $usedKeyId = $apiKey->id;

        try {
            return Crypt::decryptString($apiKey->key_hash);
        } catch (\Exception $e) {
            Log::error("Failed to decrypt API key for provider {$providerId}: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Flag an API key as exhausted or rate-limited and apply a cooldown timer.
     */
    public function flagKeyExhausted(string $keyId, int $cooldownMinutes = 60, string $reason = ''): bool
    {
        $apiKey = AIApiKey::find($keyId);

        if (! $apiKey) {
            return false;
        }

        $apiKey->update([
            'status' => 'cooldown',
            'cooldown_until' => now()->addMinutes($cooldownMinutes),
            'error_count' => ($apiKey->error_count ?? 0) + 1,
            'last_rotated_at' => now(),
        ]);

        Log::warning("API Key [{$apiKey->name}] for provider [{$apiKey->provider_id}] flagged in cooldown for {$cooldownMinutes} minutes. Reason: {$reason}");

        return true;
    }

    /**
     * Retrieve all keys for a provider with rotation telemetry.
     */
    public function getProviderKeys($providerId)
    {
        return AIApiKey::where('provider_id', $providerId)->orderByDesc('is_active')->orderBy('last_used_at', 'desc')->get();
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
