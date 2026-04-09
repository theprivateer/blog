<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Models\Visit;
use Privateer\Basecms\Services\VisitClassifier;
use Tests\TestCase;

class ReclassifyVisitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reclassify_visits_command_reprocesses_all_visits(): void
    {
        $aiVisit = Visit::factory()->create([
            'user_agent' => 'Mozilla/5.0 (compatible; GPTBot/1.0; +https://openai.com/gptbot)',
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'classification_source' => VisitClassifier::SOURCE_FALLBACK,
        ]);

        $browserVisit = Visit::factory()->create([
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Safari/537.36',
            'visitor_type' => VisitClassifier::TYPE_OTHER_BOT,
            'classification_source' => VisitClassifier::SOURCE_CRAWLER_DETECT,
        ]);

        $this->artisan('basecms:reclassify-visits')
            ->expectsOutputToContain('Reclassifying 2 visits...')
            ->expectsOutputToContain('Processed 2 visits.')
            ->assertSuccessful();

        $this->assertSame(VisitClassifier::TYPE_AI_CRAWLER, $aiVisit->fresh()->visitor_type);
        $this->assertSame('GPTBot', $aiVisit->fresh()->visitor_label);
        $this->assertSame(VisitClassifier::TYPE_LIKELY_HUMAN, $browserVisit->fresh()->visitor_type);
    }

    public function test_reclassify_visits_command_handles_empty_dataset(): void
    {
        $this->artisan('basecms:reclassify-visits')
            ->expectsOutput('No visits found. Nothing to reclassify.')
            ->assertSuccessful();
    }

    public function test_reclassify_visits_command_only_reprocesses_visits_for_the_selected_site(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $selectedSite = Site::factory()->create([
            'name' => 'Alpha Site',
            'key' => 'alpha',
        ]);
        $otherSite = Site::factory()->create([
            'name' => 'Beta Site',
            'key' => 'beta',
        ]);

        $selectedVisit = Visit::factory()->create([
            'site_id' => $selectedSite->id,
            'user_agent' => 'Mozilla/5.0 (compatible; GPTBot/1.0; +https://openai.com/gptbot)',
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'classification_source' => VisitClassifier::SOURCE_FALLBACK,
        ]);
        $otherVisit = Visit::factory()->create([
            'site_id' => $otherSite->id,
            'user_agent' => 'Mozilla/5.0 (compatible; GPTBot/1.0; +https://openai.com/gptbot)',
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'classification_source' => VisitClassifier::SOURCE_FALLBACK,
        ]);

        $this->artisan('basecms:reclassify-visits')
            ->expectsQuestion('Which site should this command run for?', 'alpha')
            ->expectsOutputToContain('Selected site: Alpha Site (alpha)')
            ->expectsOutputToContain('Processed 1 visits.')
            ->assertSuccessful();

        $this->assertSame(VisitClassifier::TYPE_AI_CRAWLER, $selectedVisit->fresh()->visitor_type);
        $this->assertSame(VisitClassifier::TYPE_LIKELY_HUMAN, $otherVisit->fresh()->visitor_type);
    }
}
