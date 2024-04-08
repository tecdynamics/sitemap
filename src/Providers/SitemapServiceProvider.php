<?php

namespace Tec\Sitemap\Providers;

use Tec\Base\Events\CreatedContentEvent;
use Tec\Base\Events\DeletedContentEvent;
use Tec\Base\Events\UpdatedContentEvent;
use Tec\Base\Supports\ServiceProvider;
use Tec\Base\Traits\LoadAndPublishDataTrait;
use Tec\Sitemap\Sitemap;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;

class SitemapServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected bool $defer = true;

    public function register(): void
    {
        $this->app->bind('sitemap', function ($app) {
            $config = config('packages.sitemap.config');

            return new Sitemap(
                $config,
                $app[Repository::class],
                $app['config'],
                $app['files'],
                $app[ResponseFactory::class],
                $app['view']
            );
        });

        $this->app->alias('sitemap', Sitemap::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('packages/sitemap')
            ->loadAndPublishConfigurations(['config'])
            ->loadAndPublishViews()
            ->publishAssets();

        $this->app['events']->listen([
            CreatedContentEvent::class,
            UpdatedContentEvent::class,
            DeletedContentEvent::class,
        ], function () {
            cache()->forget('cache_site_map_key');
        });
    }

    public function provides(): array
    {
        return ['sitemap', Sitemap::class];
    }
}
