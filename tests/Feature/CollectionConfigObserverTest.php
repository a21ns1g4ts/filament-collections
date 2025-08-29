<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;

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

it('it removes inverse relationship and data when field is deleted', function () {
    // Arrange
    $postsConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            [
                'name' => 'tags',
                'type' => 'collection',
                'relationship_type' => 'hasMany',
                'target_collection_key' => 'tags',
            ],
        ],
    ]);

    $tagsConfig = CollectionConfig::factory()->create([
        'key' => 'tags',
        'schema' => [],
    ]);

    // Trigger the creation of the inverse relationship
    $postsConfig->save();
    $tagsConfig->refresh();

    $post = CollectionData::factory()->create([
        'collection_config_id' => $postsConfig->id,
        'payload' => ['uuid' => 'post-1', 'tags' => ['tag-1']],
    ]);

    $tag = CollectionData::factory()->create([
        'collection_config_id' => $tagsConfig->id,
        'payload' => ['uuid' => 'tag-1', 'post' => 'post-1'],
    ]);

    // Act
    $postsConfig->schema = [];
    $postsConfig->save();

    // Assert
    $tagsConfig->refresh();
    $post->refresh();
    $tag->refresh();

    // Check if inverse schema is removed
    expect($tagsConfig->schema)->toBeEmpty();

    // Check if data is cleaned up
    expect($post->payload)->not->toHaveKey('tags');
    expect($tag->payload)->not->toHaveKey('post');
});

it('it creates inverse hasOne relationship', function () {
    $userConfig = CollectionConfig::factory()->create([
        'key' => 'users',
        'schema' => [],
    ]);

    $profileConfig = CollectionConfig::factory()->create([
        'key' => 'profiles',
        'schema' => [],
    ]);

    $userConfig->schema = [
        [
            'name' => 'profile',
            'type' => 'collection',
            'relationship_type' => 'hasOne',
            'target_collection_key' => 'profiles',
        ],
    ];

    $userConfig->save();

    $profileConfig->refresh();

    $inverseField = collect($profileConfig->schema)->firstWhere('name', 'user');

    expect($inverseField)->not->toBeNull();
    expect($inverseField['relationship_type'])->toBe('hasOne');
    expect($inverseField['target_collection_key'])->toBe('users');
    expect($inverseField['inverse_relationship_name'])->toBe('profile');
});
