<?php

namespace Jonassiewertsen\Jobs;

use Illuminate\Support\Facades\Artisan;
use Jonassiewertsen\Jobs\Queue\Failed\StatamicEntryFailedJobProvider;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->publishAssets();

        if (config('queue.failed.driver') === 'statamic') {
            $this->app->singleton('queue.failer', function ($app) {
                return new StatamicEntryFailedJobProvider($app['config']['queue.failed']);
            });
        }

        Statamic::afterInstalled(function () {
            Artisan::call('vendor:publish --tag=jobs-blueprints');
            Artisan::call('vendor:publish --tag=jobs-collections');
        });
    }

    private function publishAssets(): void
    {
        if ($this->app->runningInConsole()) {
            // Blueprints
            $this->publishes([
                __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
            ], 'jobs-blueprints');

            // Collections
            $this->publishes([
                __DIR__.'/../resources/collections' => base_path('content/collections'),
            ], 'jobs-collections');
        }
    }
}
