<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | Meilisearch in production (blueprint 2.12 — never LIKE-query products),
    | the lightweight "database" driver under testing so the suite needs no
    | external service.
    |
    | Supported: "algolia", "meilisearch", "typesense",
    |            "database", "collection", "null"
    */

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    */

    'soft_delete' => true,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Database Driver
    |--------------------------------------------------------------------------
    */

    'database' => [
        'connection' => env('SCOUT_DATABASE_CONNECTION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            \App\Models\Product::class => [
                'filterableAttributes' => [
                    'category_id',
                    'brand_id',
                    'status',
                    'is_featured',
                ],
                'sortableAttributes' => [
                    'sort_order',
                ],
            ],
        ],
    ],

];
