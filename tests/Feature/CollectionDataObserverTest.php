<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;

it('removes belongsTo reference when related item is deleted', function () {
    // Arrange: Create collection configurations
    $authorConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
        ],
    ]);

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

    // Arrange: Create data
    $authorData = CollectionData::factory()->create([
        'collection_config_id' => $authorConfig->id,
        'payload' => [
            'uuid' => 'author-uuid-1',
            'name' => 'John Doe',
        ],
    ]);

    $postData = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => [
            'uuid' => 'post-uuid-1',
            'title' => 'My First Post',
            'author' => 'author-uuid-1',
        ],
    ]);

    // Act: Delete the author
    $authorData->delete();

    // Assert: Reload post and check if author reference is null
    dd($postData->fresh()->payload);
    expect($postData->payload['author'])->toBeNull();
});

it('removes belongsToMany reference when related item is deleted', function () {
    // Arrange: Create collection configurations
    $tagConfig = CollectionConfig::factory()->create([
        'key' => 'tags',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
        ],
    ]);

    $postConfig = CollectionConfig::factory()->create([
        'key' => 'posts_with_tags',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
            [
                'name' => 'tags',
                'type' => 'collection',
                'relationship_type' => 'belongsToMany',
                'target_collection_key' => 'tags',
            ],
        ],
    ]);

    // Arrange: Create data
    $tag1 = CollectionData::factory()->create([
        'collection_config_id' => $tagConfig->id,
        'payload' => ['uuid' => 'tag-uuid-1', 'name' => 'Laravel'],
    ]);

    $tag2 = CollectionData::factory()->create([
        'collection_config_id' => $tagConfig->id,
        'payload' => ['uuid' => 'tag-uuid-2', 'name' => 'PHP'],
    ]);

    $postData = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => [
            'uuid' => 'post-uuid-2',
            'title' => 'My Tagged Post',
            'tags' => ['tag-uuid-1', 'tag-uuid-2'],
        ],
    ]);

    // Act: Delete one of the tags
    $tag1->delete();

    // Assert: Reload post and check if the tag reference is removed from the array
    dd($postData->fresh()->payload);
    expect($postData->payload['tags'])->not->toContain('tag-uuid-1');
    expect($postData->payload['tags'])->toContain('tag-uuid-2');
    expect(count($postData->payload['tags']))->toBe(1);
});