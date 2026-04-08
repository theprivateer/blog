---
title: "Laravel's Terminable Middleware: Run Code After the Response"
intro: null
published_at: 2026-03-30T00:00:00+10:00
category_id: 2
created_at: 2026-03-30T12:34:51+10:00
updated_at: 2026-04-01T12:55:09+10:00
metadata:
    title: null
    description: 'Learn how Laravel terminable middleware runs code after sending the response, with examples, lifecycle details, singleton state, and best uses.'
---

Most Laravel middleware runs before the response is sent to the browser. You check authentication, validate CSRF tokens, set headers. The request comes in, your middleware does its thing, and the response goes out. But some work doesn't need to happen before the response. It just needs to happen.

Laravel's terminable middleware lets you run code *after* the response has been sent to the client. The user gets their page, and your application quietly finishes up in the background.

## What terminable middleware actually does

A standard middleware has a `handle()` method. A terminable middleware adds a `terminate()` method. The difference is *when* each runs in the PHP lifecycle.

When a request hits your Laravel app, the HTTP kernel boots the application, runs the middleware pipeline, executes the controller, and builds a response. That response is sent to the client. In a traditional PHP-FPM setup, that would normally be the end of the script's life. But Laravel's HTTP kernel calls `terminate()` on the application after sending the response, which in turn calls `terminate()` on any middleware that defines it.

With PHP-FPM, the `fastcgi_finish_request()` function is what makes this possible. It flushes the response to the web server, closing the connection to the client, but the PHP process keeps running. Any code after that point executes without the user waiting for it. Laravel calls this function internally before running the termination callbacks.

The result: your user gets a fast response, and your app does its housekeeping afterwards.

## A real example: visit tracking

On this site, I track page visits for anonymous users. That means a database write on every request. It's a cheap operation, but there's no reason the visitor should wait for it. The visit data doesn't affect the response at all.

Here's the middleware:

```php
namespace Privateer\Basecms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Privateer\Basecms\Services\VisitTrackingService;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisits
{
    public function __construct(private VisitTrackingService $visitTrackingService) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! config('basecms.visits.track_visits')) {
            return;
        }

        if ($request->user()) {
            return;
        }

        $this->visitTrackingService->trackVisit($request);
    }
}
```

The `handle()` method does nothing. It just passes the request along. All the real work happens in `terminate()`, which runs after the response has already been sent. It checks whether tracking is enabled, skips authenticated users, and then records the visit.

The tracking service itself does a simple `Visit::create()` with the path, method, IP address, session ID, and user agent. That database insert now happens entirely outside the request/response cycle.

## Other good candidates

Terminable middleware works well for any side-effect that doesn't influence the response.

**Logging and metrics.** If you're recording response times, status codes, or request metadata to an external service like Datadog or a logging pipeline, you want the full picture (including the response status) but you don't want the logging call to slow down the response. A `terminate()` method receives both the `Request` and `Response` objects, so you have everything you need.

**Session cleanup or cookie bookkeeping.** If you need to write audit records about session activity or update "last seen" timestamps, these don't affect what the user sees. Deferring them to termination keeps the perceived response time lower.

## Preserving state between handle() and terminate()

In the visit tracking example, `handle()` doesn't do anything, so there's no state to carry over. But sometimes you'll want to capture something during `handle()` and use it in `terminate()`. Timing a request is the classic case: you'd start a timer in `handle()` and record the elapsed time in `terminate()`.

There's a catch. By default, Laravel resolves a fresh instance of the middleware for `terminate()`. Any properties you set in `handle()` won't be there.

The fix is to register the middleware as a singleton in your `AppServiceProvider`:

```php
use App\Http\Middleware\TimingMiddleware;

public function register(): void
{
    $this->app->singleton(TimingMiddleware::class);
}
```

This tells the container to reuse the same instance, so properties set during `handle()` are still available when `terminate()` runs:

```php
class TimingMiddleware
{
    private float $startTime;

    public function handle(Request $request, Closure $next): Response
    {
        $this->startTime = microtime(true);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $duration = microtime(true) - $this->startTime;

        Log::info('Request duration', [
            'path' => $request->path(),
            'duration_ms' => round($duration * 1000, 2),
            'status' => $response->getStatusCode(),
        ]);
    }
}
```

Without the singleton registration, `$this->startTime` would be uninitialised in `terminate()` and you'd get an error.

## When not to use it

Terminable middleware isn't the right tool when the work affects the response. If you need to modify headers, set cookies, or change the response body based on some computation, that has to happen in `handle()`. The response is already gone by the time `terminate()` runs.

It's also worth noting that the PHP process is still occupied during termination. If your `terminate()` method does something slow, like calling an external API that takes several seconds, it ties up that PHP-FPM worker. For heavy background processing, a queued job is still the better choice. Terminable middleware is best for quick fire-and-forget operations where the overhead of dispatching a job would be more than the work itself.