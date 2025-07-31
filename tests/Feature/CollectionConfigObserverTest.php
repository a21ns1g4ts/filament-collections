<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;

it('it creates inverse relationship if it does not exist', function () {
    $postsConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [],
    ]);

    $tagsConfig = CollectionConfig::factory()->create([
        'key' => 'tags',
        'schema' => [],
    ]);

    $postsConfig->schema = [
        [
            'name' => 'tags',
            'type' => 'collection',
            'relationship_type' => 'hasMany',
            'target_collection_key' => 'tags',
        ],
    ];

    $postsConfig->save();

    $tagsConfig->refresh();

    $inverseField = collect($tagsConfig->schema)->firstWhere('name', 'post');

    expect($inverseField)->not->toBeNull();
    expect($inverseField['relationship_type'])->toBe('belongsTo');
    expect($inverseField['target_collection_key'])->toBe('posts');
    expect($inverseField['inverse_relationship_name'])->toBe('tags');
});

it('it updates inverse relationship name if it already exists', function () {
    $postsConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [],
    ]);

    $tagsConfig = CollectionConfig::factory()->create([
        'key' => 'tags',
        'schema' => [
            [
                'name' => 'posts_relation',
                'type' => 'collection',
                'relationship_type' => 'belongsTo',
                'target_collection_key' => 'posts',
            ],
        ],
    ]);

    $postsConfig->schema = [
        [
            'name' => 'tags',
            'type' => 'collection',
            'relationship_type' => 'hasMany',
            'target_collection_key' => 'tags',
            'inverse_relationship_name' => 'posts_relation',
        ],
    ];

    $postsConfig->save();

    $tagsConfig->refresh();

    $inverseField = collect($tagsConfig->schema)->firstWhere('name', 'posts_relation');

    expect($inverseField)->not->toBeNull();
    expect($inverseField['inverse_relationship_name'])->toBe('tags');
    // Ensure no new field was created
    expect(count($tagsConfig->schema))->toBe(1);
});
