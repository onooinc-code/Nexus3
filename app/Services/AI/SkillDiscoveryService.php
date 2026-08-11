<?php

namespace App\Services\AI;

class SkillDiscoveryService
{
    protected $ragFlow;

    public function __construct(RAGFlowClient $ragFlow)
    {
        $this->ragFlow = $ragFlow;
    }

    /***     * Sync a skill to RAGFlow (Upsert)
     */
    public function syncSkillToRAG(array $skillData)
    {
        return $this->ragFlow->post('/v1/upsert', [
            'data' => $skillData,
        ]);
    }

    /***     * Find relevant skills based on query/context
     */
    public function findRelevantSkills(string $query, int $limit = 5)
    {
        return $this->ragFlow->post('/v1/retrieve', [
            'query' => $query,
            'limit' => $limit,
        ]);
    }
}
