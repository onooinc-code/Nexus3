<?php

namespace Database\Seeders;

use App\Models\CredentialsHub\Credential;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CredentialsHubSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = public_path('nexus-hub/data/credentials.json');

        if (File::exists($jsonPath)) {
            $items = json_decode(File::get($jsonPath), true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    Credential::updateOrCreate(
                        ['title' => $item['title']],
                        [
                            'category' => $item['category'],
                            'subtitle' => $item['subtitle'] ?? 'System Resource',
                            'icon' => $item['icon'] ?? 'fa-solid fa-key',
                            'icon_bg' => $item['iconBg'] ?? 'bg-green-500/10 text-green-400 border-green-500/20',
                            'test_status' => $item['testStatus'] ?? 'success',
                            'test_code' => $item['testCode'] ?? '200 OK',
                            'fields' => $item['fields'] ?? [],
                            'last_tested_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
