<?php

namespace Tests\Feature\Mcp;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Privateer\Basecms\Mcp\BasecmsMcpServer;
use Privateer\Basecms\Mcp\Support\McpAccess;
use Privateer\Basecms\Mcp\Tools\AnalyticsClassificationTool;
use Privateer\Basecms\Mcp\Tools\AnalyticsOverviewTool;
use Privateer\Basecms\Mcp\Tools\AnalyticsTopPathsTool;
use Privateer\Basecms\Models\Visit;
use Privateer\Basecms\Services\VisitClassifier;
use Tests\TestCase;

class AnalyticsToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_overview_tool_returns_totals(): void
    {
        Visit::factory()->count(3)->create();

        $response = BasecmsMcpServer::tool(AnalyticsOverviewTool::class, []);

        $response->assertOk();
        $response->assertStructuredContent(fn ($json) => $json
            ->where('total_visits', 3)
            ->etc());
    }

    public function test_analytics_overview_tool_denies_without_ability(): void
    {
        // With no analytics:read ability, shouldRegister() hides the tool entirely
        // (same check as the internal guard), so calling it directly still errors.
        app()->instance(McpAccess::class, new McpAccess(['posts:read']));

        $response = BasecmsMcpServer::tool(AnalyticsOverviewTool::class, []);

        $response->assertHasErrors();
    }

    public function test_analytics_top_paths_tool_returns_paths(): void
    {
        Visit::factory()->create(['path' => '/blog/hello']);
        Visit::factory()->create(['path' => '/blog/hello']);
        Visit::factory()->create(['path' => '/notes']);

        $response = BasecmsMcpServer::tool(AnalyticsTopPathsTool::class, ['limit' => 5]);

        $response->assertOk()->assertSee('/blog/hello');
    }

    public function test_analytics_classification_tool_returns_breakdown(): void
    {
        Visit::factory()->create(['visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN]);
        Visit::factory()->create(['visitor_type' => VisitClassifier::TYPE_AI_CRAWLER]);

        $response = BasecmsMcpServer::tool(AnalyticsClassificationTool::class, []);

        $response->assertOk()->assertSee('Likely human')->assertSee('AI crawler');
    }

    public function test_analytics_classification_tool_denies_without_ability(): void
    {
        app()->instance(McpAccess::class, new McpAccess(['posts:read']));

        $response = BasecmsMcpServer::tool(AnalyticsClassificationTool::class, []);

        $response->assertHasErrors();
    }
}
