<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;

it('automatically adds hasMany inverse relationship when belongsTo is defined', function () {
    // Arrange: Create author config
    $authorConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
        ],
    ]);

    // Act: Create post config with belongsTo relationship to authors
    $postConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
            [
                'name' => 'author',
                'type' => 'collection',
                'relationship_type' => 'belongsTo',
                'target_collection_key' => 'authors',
            ],
        ],
    ]);

    // Assert: Reload author config and check for the new hasMany relationship
    $authorConfig->refresh();

    $hasManyRelationship = collect($authorConfig->schema)->first(function ($field) {
        return ($field['name'] ?? null) === 'posts' && ($field['relationship_type'] ?? null) === 'hasMany';
    });

    expect($hasManyRelationship)->not->toBeNull();
    expect($hasManyRelationship['target_collection_key'])->toBe('posts');
    expect($hasManyRelationship['foreign_key_on_target'])->toBe('author');
});
