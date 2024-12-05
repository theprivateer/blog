<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MicropubController extends Controller
{
    public function getCapabilities(Request $request)
    {
        // return empty JSON object with 200 status code
        return response()->json(null);
    }

    public function publish(Request $request)
    {
        // Process the post payload
        $data = json_decode($request->getContent());
        $title = $data->properties->name[0];
        $body = trim($data->properties->content[0]);
        $link = null;

        // is it a link
        $lines = explode("\n", $body);

        // Check for an inline title
        if (strpos($lines[0], '# ') === 0) {
            $title = array_shift($lines);
            $title = ltrim($title, '# ');
        }

        // Check for a link within the first line or two
        if (strpos($lines[0], '<http') === 0) {
            $link = array_shift($lines);
            $link = ltrim($link, '<');
            $link = rtrim($link, '>');
        }

        // The rest is the body - stick it back together
        $body = implode("\n", $lines);

        // Generate the frontmatter
        $content = ['---'];
        $content[] = 'title: "' . $title . '"';

        if ($link) {
            $content[] = "link: {$link}";
        }

        $content[] = '---';
        $content[] = $body;

        $filename = now()->format('Y-m-d\TH:i:s') . '.' . Str::slug($title) . '.md';

        Storage::disk('posts')->put($filename, implode("\n", $content));

        return response(
            null,
            201,
            ['Location' => route('posts.show', Str::slug($title))]
        );
    }
}
