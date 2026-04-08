<?php

namespace App\StaticSite;

use App\Models\Note;
use Privateer\Basecms\Services\SiteManager;
use Privateer\Basecms\StaticSite\StaticRoute;
use Privateer\Basecms\StaticSite\StaticRouteExporter;

class NoteStaticRouteExporter implements StaticRouteExporter
{
    public function __construct(private readonly SiteManager $siteManager) {}

    /**
     * @return iterable<StaticRoute>
     */
    public function export(): iterable
    {
        $routes = [];
        $site = $this->siteManager->required();
        $pageCount = max(1, (int) ceil(Note::query()->forSite($site)->count() / max(1, (new Note)->getPerPage())));

        for ($page = 1; $page <= $pageCount; $page++) {
            $sourceUri = $page === 1 ? '/notes' : '/notes?page='.$page;
            $publicUri = $page === 1 ? '/notes/' : '/notes/page/'.$page.'/';
            $outputPath = $page === 1 ? 'notes/index.html' : 'notes/page/'.$page.'/index.html';

            $routes[] = StaticRoute::html($sourceUri, $publicUri, $outputPath, 'notes.index');
        }

        foreach (Note::query()->forSite($site)->latest()->get() as $note) {
            $routes[] = StaticRoute::html(
                sourceUri: route('notes.show', $note, false),
                publicUri: route('notes.show', $note, false).'/',
                outputPath: 'notes/'.$note->slug.'/index.html',
                routeName: 'notes.show',
                routeParameters: ['note' => $note->slug],
            );
        }

        return $routes;
    }
}
