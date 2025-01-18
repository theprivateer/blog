<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\PostPublished;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Spatie\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Storage;

class MicropubService
{
    public function getCapabilities(Request $request): JsonResponse
    {
        // return empty JSON object with 200 status code
        return response()->json(null);
    }

    public function processRequest(Request $request): Response
    {
        // Process files (images)
        if ($request->hasFile('file')) {
            return $this->processImage($request);
        }

        $data = json_decode($request->getContent());
        $filename = Str::of($data->properties->name[0])->slug();
        $body = trim($data->properties->content[0]);

        $lines = explode("\n", $body);

        // is it a slash page?
        foreach ($lines as $line) {
            if (strpos($line, '# ') === 0) {
                // Check for an inline title
                $title = ltrim($line, '# ');
                array_shift($lines);
            } elseif (strpos($line, '<http') === 0) {
                // Check for a link either on the first line, or second if
                // there was an inline title
                // iA Writer encloses URLs in <> when publishing via Micropub
                // A regex would be more elegant here, but this does the job
                $link = ltrim($line, '<');
                $link = rtrim($link, '>');
                array_shift($lines);
            } elseif (strpos($line, '/') === 0) {
                // Check for a parent path (for nested slash pages)
                $path = $line;
                array_shift($lines);
            } else {
                break;
            }
        }

        $body = implode("\n", $lines);

        if (isset($path)) {
            return $this->updateSlashPage(
                filename: $filename,
                body: $body,
                title: $title ?? null,
                path: $path ?? null
            );
        }

        // Determine whether this is a post or slashpage and handle accordingly
        $slashes = Sheets::collection('slashes')
                        ->all()
                        ->pluck('slug')
                        ->all();

        if (in_array($filename, $slashes)) {
            return $this->updateSlashPage(
                filename: $filename,
                body: $body,
                title: $title ?? null
            );
        }

        return $this->updatePost(
            filename: $filename,
            body: $body,
            title: $title ?? null,
            link: $link ?? null
        );
    }

    private function updatePost(string $filename, string $body, ?string $title = null, ?string $link = null): Response
    {
        // Generate the frontmatter
        // Note: even if frontmatter is set in the document
        // iA Writer will not send it through in the payload
        $content = ['---'];
        if ($title) {
            $content[] = 'title: "' . $title . '"';
        }
        if ($link) {
            $content[] = "link: {$link}";
        }

        // Does the file already exist?
        $exists = Sheets::collection('posts')
            ->all()
            ->where('slug', $filename)
            ->first();

        if ($exists) {
            $filename = $exists->getPath();
            $content[] = 'modified: ' . now()->format('Y-m-d\TH:i:s');
            $type = 'update';
        } else {
            $filename = now()->format('Y-m-d\TH:i:s') . '.' . $filename . config('sheets.collections.posts.extension');
            $type = 'publish';
        }

        $content[] = '---';
        $content[] = $body;

        Storage::disk('posts')->put(
            $filename,
            implode("\n", $content)
        );

        $post = Sheets::collection('posts')->get($filename);

        event(new PostPublished(post: $post, type: $type));

        return response(
            null,
            201,
            ['Location' => route('posts.show', $post->slug)]
        );
    }

    private function updateSlashPage(string $filename, string $body, ?string $title = null, ?string $path = null): Response
    {
        $title = $title ?: ucwords($filename);

        // Generate the frontmatter
        // Note: even if frontmatter is set in the document
        // iA Writer will not send it through in the payload
        $content = ['---'];
        $content[] = 'title: "' . $title . '"';
        $content[] = 'modified: ' . now()->format('Y-m-d\TH:i:s');
        $content[] = '---';
        $content[] = $body;

        $filename = ($path) ? ltrim($path, '/') . '/' . $filename : $filename;

        Storage::disk('slashes')->put(
            $filename . '.' . config('sheets.collections.posts.extension'),
            implode("\n", $content)
        );

        $post = Sheets::collection('slashes')->get($filename);

        event(new PostPublished(post: $post));

        return response(
            null,
            201,
            ['Location' => route('slashes.show', $post->slug)]
        );
    }

    private function processImage(Request $request): Response
    {
        $path = $request->file->storeAs(
            'images/' . now()->format('Y/m'),
            $request->file->getClientOriginalName(),
            'public'
        );

        // @TODO: implement Glide for image optimisation and return a Glide URL here
        return response(
            null,
            201,
            ['Location' => asset('storage/' . $path)]
        );
    }

}
