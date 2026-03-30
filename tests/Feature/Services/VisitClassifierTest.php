<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Privateer\Basecms\Services\VisitClassifier;
use Tests\TestCase;

class VisitClassifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_ai_crawler_is_classified_from_app_rules(): void
    {
        $classification = app(VisitClassifier::class)->classify('Mozilla/5.0 (compatible; GPTBot/1.0; +https://openai.com/gptbot)');

        $this->assertSame(VisitClassifier::TYPE_AI_CRAWLER, $classification['visitor_type']);
        $this->assertSame('GPTBot', $classification['visitor_label']);
        $this->assertSame(VisitClassifier::SOURCE_AI_RULES, $classification['classification_source']);
    }

    public function test_search_crawler_is_classified_from_detector(): void
    {
        $classification = app(VisitClassifier::class)->classify('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        $this->assertSame(VisitClassifier::TYPE_SEARCH_CRAWLER, $classification['visitor_type']);
        $this->assertSame('Googlebot', $classification['visitor_label']);
        $this->assertSame(VisitClassifier::SOURCE_CRAWLER_DETECT, $classification['classification_source']);
    }

    public function test_generic_bot_is_classified_as_other_bot(): void
    {
        $classification = app(VisitClassifier::class)->classify('facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');

        $this->assertSame(VisitClassifier::TYPE_OTHER_BOT, $classification['visitor_type']);
        $this->assertSame(VisitClassifier::SOURCE_CRAWLER_DETECT, $classification['classification_source']);
    }

    public function test_browser_user_agent_is_classified_as_likely_human(): void
    {
        $classification = app(VisitClassifier::class)->classify('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Safari/537.36');

        $this->assertSame(VisitClassifier::TYPE_LIKELY_HUMAN, $classification['visitor_type']);
        $this->assertNull($classification['visitor_label']);
        $this->assertSame(VisitClassifier::SOURCE_FALLBACK, $classification['classification_source']);
    }

    public function test_empty_user_agent_is_classified_as_unknown(): void
    {
        $classification = app(VisitClassifier::class)->classify(null);

        $this->assertSame(VisitClassifier::TYPE_UNKNOWN, $classification['visitor_type']);
        $this->assertNull($classification['visitor_label']);
        $this->assertSame(VisitClassifier::SOURCE_FALLBACK, $classification['classification_source']);
    }

    public function test_ai_specific_rules_win_over_generic_crawler_detection(): void
    {
        $classification = app(VisitClassifier::class)->classify('Mozilla/5.0 (compatible; ClaudeBot/1.0; +https://www.anthropic.com/claudebot)');

        $this->assertSame(VisitClassifier::TYPE_AI_CRAWLER, $classification['visitor_type']);
        $this->assertSame('ClaudeBot', $classification['visitor_label']);
        $this->assertSame(VisitClassifier::SOURCE_AI_RULES, $classification['classification_source']);
    }
}
