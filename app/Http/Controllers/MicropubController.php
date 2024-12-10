<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\PostPublished;
use Illuminate\Http\JsonResponse;
use Spatie\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Storage;

class MicropubController extends Controller
{
    public function getCapabilities(Request $request): JsonResponse
    {
        // return empty JSON object with 200 status code
        return response()->json(null);
    }

    public function publish(Request $request)
    {
        // @TODO: Refactor to a service

        // Process the post payload and set default values
        $data = json_decode($request->getContent());
        $title = $data->properties->name[0];
        $body = trim($data->properties->content[0]);
        $link = null;

        // Break up the body to examine the first couple of lines
        $lines = explode("\n", $body);

        // Check for an inline title
        if (strpos($lines[0], '# ') === 0) {
            $title = array_shift($lines);
            $title = ltrim($title, '# ');
        }

        // Check for a link either on the first line,
        // or second if there was an inline title
        // iA Writer encloses URLs in <> when publishing via Micropub
        // A regex would be more elegant here, but this does the job
        if (strpos($lines[0], '<http') === 0) {
            $link = array_shift($lines);
            $link = ltrim($link, '<');
            $link = rtrim($link, '>');
        }

        // The rest is the body - stick it back together
        $body = implode("\n", $lines);

        // Generate the frontmatter
        // Note: even if frontmatter is set in the document
        // iA Writer will not send it through in the payload
        $content = ['---'];
        $content[] = 'title: "' . $title . '"';

        if ($link) {
            $content[] = "link: {$link}";
        }

        $content[] = '---';
        $content[] = $body;

        $filename = now()->format('Y-m-d\TH:i:s') . '.' . Str::of($title)->slug();

        Storage::disk('posts')->put(
            $filename . '.' . config('sheets.collections.posts.extension'),
            implode("\n", $content)
        );

        $post = Sheets::collection('posts')->get($filename);

        event(new PostPublished($post));

        return response(
            null,
            201,
            ['Location' => route('posts.show', $post->slug)]
        );
    }
}
