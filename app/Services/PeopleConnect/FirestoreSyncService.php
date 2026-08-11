<?php

namespace App\Services\PeopleConnect;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirestoreSyncService
{
    protected ?string $projectId = null;

    protected ?array $serviceAccount = null;

    protected ?string $baseUrl = null;

    public function __construct()
    {
        try {
            $serviceAccountPath = config('services.firebase.service_account', base_path('nexus-c9155-firebase-adminsdk-fbsvc-be5bcfadde.json'));

            if (file_exists($serviceAccountPath)) {
                $content = file_get_contents($serviceAccountPath);
                if ($content !== false) {
                    $this->serviceAccount = json_decode($content, true);
                    $this->projectId = $this->serviceAccount['project_id'] ?? null;
                    if ($this->projectId) {
                        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FirestoreSyncService initialization fallback: '.$e->getMessage());
            $this->baseUrl = null;
        }
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl) && ! empty($this->serviceAccount);
    }

    protected function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $creds = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/datastore',
                $this->serviceAccount
            );
            $token = $creds->fetchAuthToken();

            return $token['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('Failed to fetch Firebase auth token: '.$e->getMessage());

            return null;
        }
    }

    protected function writeDocument(string $path, array $data): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        // Avoid polluting Firestore or making network calls during PHPUnit test runs
        if (app()->runningUnitTests()) {
            return true;
        }

        $token = $this->getAccessToken();
        if (! $token) {
            return false;
        }

        try {
            $fields = $this->convertToFirestoreFields($data);
            $queryParams = [];
            foreach (array_keys($data) as $field) {
                $queryParams[] = 'updateMask.fieldPaths='.urlencode((string) $field);
            }

            $url = $this->baseUrl.'/'.ltrim($path, '/').'?'.implode('&', $queryParams);

            $response = Http::withToken($token)->patch($url, [
                'fields' => $fields,
            ]);

            if (! $response->successful()) {
                Log::error("Firestore write failed for [{$path}]: ".$response->body());

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Firestore write exception for [{$path}]: ".$e->getMessage());

            return false;
        }
    }

    protected function convertToFirestoreFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = $this->convertValue($value);
        }

        return $fields;
    }

    protected function convertValue(mixed $value): array
    {
        if (is_null($value)) {
            return ['nullValue' => 'NULL_VALUE'];
        }
        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }
        if (is_int($value)) {
            return ['integerValue' => (string) $value];
        }
        if (is_float($value)) {
            return ['doubleValue' => $value];
        }
        if (is_array($value)) {
            if (empty($value) || array_is_list($value)) {
                $values = [];
                foreach ($value as $item) {
                    $values[] = $this->convertValue($item);
                }

                return ['arrayValue' => ['values' => $values]];
            }

            return ['mapValue' => ['fields' => $this->convertToFirestoreFields($value)]];
        }

        return ['stringValue' => (string) $value];
    }

    public function syncSession(array $sessionData): bool
    {
        return $this->writeDocument('sessions/'.($sessionData['name'] ?? 'default'), [
            'name' => $sessionData['name'] ?? '',
            'status' => $sessionData['status'] ?? 'STOPPED',
            'engine' => [
                'state' => $sessionData['engine']['state'] ?? '',
                'WWebVersion' => $sessionData['engine']['WWebVersion'] ?? '',
            ],
            'me' => [
                'id' => $sessionData['me']['id'] ?? '',
                'pushName' => $sessionData['me']['pushName'] ?? '',
            ],
            'updatedAt' => (int) (time() * 1000),
        ]);
    }

    public function syncContact(string $id, array $contactData): bool
    {
        return $this->writeDocument('contacts/'.$id, $contactData);
    }

    public function syncConversationOverview(string $id, array $data): bool
    {
        return $this->writeDocument('chats/'.$id, $data);
    }

    public function syncMessage(string $chatId, string $messageId, array $messageData): bool
    {
        return $this->writeDocument('chats/'.$chatId.'/messages/'.$messageId, $messageData);
    }
}
