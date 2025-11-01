<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:cache {--clear : Clear the settings cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache system settings for improved performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('clear')) {
            return $this->clearCache();
        }

        return $this->cacheSettings();
    }

    /**
     * Cache all settings.
     */
    protected function cacheSettings(): int
    {
        $this->info('Caching system settings...');

        // Clear existing cache first
        Setting::clearCache();

        // Trigger caching by getting all settings
        $settings = Setting::all();
        Cache::put(Setting::CACHE_KEY, $settings->keyBy('key'), Setting::CACHE_TTL);

        // Cache public settings separately
        $publicSettings = Setting::getPublic();
        Cache::put(Setting::CACHE_KEY.'_public', $publicSettings, Setting::CACHE_TTL);

        $this->components->info(sprintf('Cached %d settings successfully', $settings->count()));

        return Command::SUCCESS;
    }

    /**
     * Clear the settings cache.
     */
    protected function clearCache(): int
    {
        $this->info('Clearing settings cache...');

        Setting::clearCache();

        $this->components->info('Settings cache cleared successfully');

        return Command::SUCCESS;
    }
}
