<div
    class="flex items-start md:justify-end"
>
    <div
        class="w-full items-center gap-3 rounded-lg border border-gray-200 bg-white/80 px-4 py-3 text-sm text-gray-600 shadow-sm ring-1 ring-gray-950/5 backdrop-blur-sm md:max-w-xs dark:border-white/10 dark:bg-white/5 dark:text-gray-300"
        style="display: none;"
        wire:dirty.delay.flex
        wire:target="filters"
    >
        <x-filament::loading-indicator class="h-5 w-5 text-gray-500 dark:text-gray-400" />

        <span>Updating analytics...</span>
    </div>
</div>
