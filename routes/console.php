<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('export', function () {
    $records = DB::table('notes')->get();

    foreach ($records as $record) {

        $content = [];

        if ($record->image) {
            $content[] = '---';
            $content[] = '![](' . $record->image . ')';
        }

        $content[] = $record->content;

        $date = Carbon::parse($record->created_at);
        $date = explode('+', $date->format('c'));
        $filename = $date[0] . '.md';

        Storage::disk('moments')->put($filename, implode("\n", $content));
    }
});
