<?php

namespace Privateer\Basecms\Services;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Throwable;

class VisitClassifier
{
    public const TYPE_LIKELY_HUMAN = 'likely_human';

    public const TYPE_AI_CRAWLER = 'ai_crawler';

    public const TYPE_SEARCH_CRAWLER = 'search_crawler';

    public const TYPE_OTHER_BOT = 'other_bot';

    public const TYPE_UNKNOWN = 'unknown';

    public const SOURCE_AI_RULES = 'ai_rules';

    public const SOURCE_CRAWLER_DETECT = 'crawler_detect';

    public const SOURCE_VERIFIED_BOT = 'verified_bot';

    public const SOURCE_FALLBACK = 'fallback';

    /**
     * @var array<string, array<int, string>>
     */
    protected array $aiCrawlerPatterns = [
        'GPTBot' => ['gptbot'],
        'OAI-SearchBot' => ['oai-searchbot'],
        'ChatGPT-User' => ['chatgpt-user'],
        'ClaudeBot' => ['claudebot'],
        'Claude-User' => ['claude-user'],
        'PerplexityBot' => ['perplexitybot'],
        'Amazonbot' => ['amazonbot'],
        'CCBot' => ['ccbot'],
    ];

    /**
     * @var array<int, string>
     */
    protected array $searchCrawlerLabels = [
        'AdsBot-Google',
        'Applebot',
        'Bingbot',
        'DuckDuckBot',
        'Google-Extended',
        'GoogleOther',
        'Googlebot',
        'Mediapartners-Google',
        'PetalBot',
        'YandexBot',
    ];

    public function __construct(private readonly CrawlerDetect $crawlerDetect) {}

    /**
     * @return array{visitor_type: string, visitor_label: ?string, classification_source: string}
     */
    public function classify(?string $userAgent): array
    {
        $normalizedUserAgent = trim((string) $userAgent);

        if ($normalizedUserAgent === '') {
            return $this->makeClassification(self::TYPE_UNKNOWN, null, self::SOURCE_FALLBACK);
        }

        if ($aiLabel = $this->matchAiCrawler($normalizedUserAgent)) {
            return $this->makeClassification(self::TYPE_AI_CRAWLER, $aiLabel, self::SOURCE_AI_RULES);
        }

        try {
            if ($this->crawlerDetect->isCrawler($normalizedUserAgent)) {
                $label = $this->normalizeCrawlerLabel($this->crawlerDetect->getMatches());
                $visitorType = in_array($label, $this->searchCrawlerLabels, true)
                    ? self::TYPE_SEARCH_CRAWLER
                    : self::TYPE_OTHER_BOT;

                return $this->makeClassification($visitorType, $label, self::SOURCE_CRAWLER_DETECT);
            }
        } catch (Throwable) {
            return $this->makeClassification(self::TYPE_UNKNOWN, null, self::SOURCE_FALLBACK);
        }

        return $this->makeClassification(self::TYPE_LIKELY_HUMAN, null, self::SOURCE_FALLBACK);
    }

    protected function matchAiCrawler(string $userAgent): ?string
    {
        $normalizedUserAgent = mb_strtolower($userAgent);

        foreach ($this->aiCrawlerPatterns as $label => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($normalizedUserAgent, $pattern)) {
                    return $label;
                }
            }
        }

        return null;
    }

    protected function normalizeCrawlerLabel(mixed $matches): ?string
    {
        if (is_string($matches)) {
            $label = trim($matches);

            return $label !== '' ? $label : null;
        }

        if (is_array($matches)) {
            foreach ($matches as $match) {
                if (is_string($match) && trim($match) !== '') {
                    return trim($match);
                }
            }
        }

        return null;
    }

    /**
     * @return array{visitor_type: string, visitor_label: ?string, classification_source: string}
     */
    protected function makeClassification(string $visitorType, ?string $visitorLabel, string $classificationSource): array
    {
        return [
            'visitor_type' => $visitorType,
            'visitor_label' => $visitorLabel,
            'classification_source' => $classificationSource,
        ];
    }
}
