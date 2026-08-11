<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemMetadataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_routes_endpoint_returns_separated_routes(): void
    {
        $response = $this->getJson('/api/v1/system/routes');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary' => [
                        'total',
                        'api_count',
                        'web_count',
                    ],
                    'api',
                    'web',
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('data.summary.total'));
        $this->assertGreaterThan(0, $response->json('data.summary.api_count'));
    }

    public function test_system_schema_endpoint_returns_database_schema(): void
    {
        $response = $this->getJson('/api/v1/system/schema');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tables_count',
                    'tables',
                ],
            ]);

        $this->assertGreaterThanOrEqual(0, $response->json('data.tables_count'));
    }

    public function test_system_codebase_endpoint_returns_controllers_and_services(): void
    {
        $response = $this->getJson('/api/v1/system/codebase');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary' => [
                        'controllers_count',
                        'services_count',
                    ],
                    'controllers',
                    'services',
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('data.summary.controllers_count'));
        $this->assertGreaterThan(0, $response->json('data.summary.services_count'));
    }

    public function test_system_docs_endpoint_returns_documentation_index(): void
    {
        $response = $this->getJson('/api/v1/system/docs');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_docs',
                    'files',
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('data.total_docs'));
    }

    public function test_system_views_endpoint_returns_blade_templates_list(): void
    {
        $response = $this->getJson('/api/v1/system/views');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_views',
                    'views',
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('data.total_views'));
    }

    public function test_system_explorer_web_route_renders_successfully(): void
    {
        $response = $this->get('/system');

        $response->assertOk()
            ->assertSee('Nexus System Explorer');
    }

    public function test_system_readme_api_endpoint_returns_json(): void
    {
        $response = $this->getJson('/api/v1/system/readme');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'exists',
                    'filename',
                    'relative_path',
                    'size_bytes',
                    'last_modified',
                    'content',
                ],
            ]);
    }

    public function test_system_readme_endpoint_returns_raw_text(): void
    {
        $response = $this->get('/system/readme?format=raw');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('NexusV3');
    }

    public function test_system_readme_web_route_renders_successfully(): void
    {
        $response = $this->get('/system/readme');

        $response->assertOk()
            ->assertSee('NexusV3 System Readme');
    }
}
