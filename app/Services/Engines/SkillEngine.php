<?php

namespace App\Services\Engines;

use App\Services\AI\SkillDiscoveryService;
use Illuminate\Support\Facades\Log;

class SkillEngine
{
    protected $skillService;

    public function __construct(SkillDiscoveryService $skillService)
    {
        $this->skillService = $skillService;
    }

    public function store(array $data): array
    {
        // delegate to service
        $skill = $this->skillService->syncSkillToRAG($data);

        Log::info('Skill stored in RAGFlow', ['skill' => $data['name'] ?? 'not-set']);

        return [
            'success' => true,
            'skill' => $skill,
        ];
    }

    public function search(string $query, array $criteria = []): array
    {
        $limit = $criteria['limit'] ?? 5;
        $skills = $this->skillService->findRelevantSkills($query, $limit);

        return [
            'success' => true,
            'skills' => $skills,
        ];
    }
}
