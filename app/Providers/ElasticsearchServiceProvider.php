<?php

namespace App\Providers;

use App\Search\ElasticsearchEngine;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts([config('scout.elasticsearch.hosts')[0]])
                ->build();
        });
    }

    public function boot()
    {
        resolve(EngineManager::class)->extend('elasticsearch', function () {
            return new ElasticsearchEngine(
                $this->app->make(Client::class),
                config('scout.prefix').'products'
            );
        });
    }
} 