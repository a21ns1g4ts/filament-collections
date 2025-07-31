<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Validation\ValidationException;

it('it prevents assigning a hasOne related item that is already assigned', function () {
    $userConfig = CollectionConfig::factory()->create([
        'key' => 'users',
        'schema' => [
            [
                'name' => 'profile',
                'type' => 'collection',
                'relationship_type' => 'hasOne',
                'target_collection_key' => 'profiles',
            ],
        ],
    ]);

    $profileConfig = CollectionConfig::factory()->create([
        'key' => 'profiles',
        'schema' => [],
    ]);

    $profile = CollectionData::factory()->create([
        'collection_config_id' => $profileConfig->id,
        'payload' => ['uuid' => 'profile-1'],
    ]);

    // First user successfully gets the profile
    $user1 = CollectionData::factory()->create([
        'collection_config_id' => $userConfig->id,
        'payload' => ['uuid' => 'user-1', 'profile' => 'profile-1'],
    ]);

    // Expect exception when second user tries to get the same profile
    $this->expectException(ValidationException::class);

    CollectionData::factory()->create([
        'collection_config_id' => $userConfig->id,
        'payload' => ['uuid' => 'user-2', 'profile' => 'profile-1'],
    ]);
});

it('removes belongsTo reference when related item is deleted', function () {
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

    $authorConfig->refresh();

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

    $authorData->delete();

    $postData->refresh();

    expect($postData->authors)->toBeNull();
    expect(@$postData->payload['author'])->toBeNull();
});

it('removes hasMany reference when related item is deleted', function () {
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
                'relationship_type' => 'hasMany',
                'target_collection_key' => 'tags',
            ],
        ],
    ]);

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

    $tag1->delete();

    $postData->refresh();

    expect($postData->payload['tags'])->not->toContain('tag-uuid-1');
    expect($postData->payload['tags'])->toContain('tag-uuid-2');
    expect(count($postData->payload['tags']))->toBe(1);
});

it('updates inverse hasMany relationship when item is saved', function () {
    $postConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
            [
                'name' => 'tags',
                'type' => 'collection',
                'relationship_type' => 'hasMany',
                'target_collection_key' => 'tags',
                'inverse_relationship_name' => 'posts',
            ],
        ],
    ]);

    $tagConfig = CollectionConfig::factory()->create([
        'key' => 'tags',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
            [
                'name' => 'posts',
                'type' => 'collection',
                'relationship_type' => 'hasMany',
                'target_collection_key' => 'posts',
                'inverse_relationship_name' => 'tags',
            ],
        ],
    ]);

    $postData = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => [
            'uuid' => 'post-uuid-3',
            'title' => 'Post about tags',
            'tags' => [],
        ],
    ]);

    $tagData = CollectionData::factory()->create([
        'collection_config_id' => $tagConfig->id,
        'payload' => [
            'uuid' => 'tag-uuid-3',
            'name' => 'Relationships',
            'posts' => [],
        ],
    ]);

    $payload = $postData->payload;
    $payload['tags'] = ['tag-uuid-3'];
    $postData->payload = $payload;
    $postData->save();

    $tagData->refresh();

    expect($tagData->payload['posts'])->toContain('post-uuid-3');
});

it('updates inverse hasMany relationship when belongsTo item is saved', function () {
    $authorConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
            [
                'name' => 'posts',
                'type' => 'collection',
                'relationship_type' => 'hasMany',
                'target_collection_key' => 'posts',
                'inverse_relationship_name' => 'author',
            ],
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
                'inverse_relationship_name' => 'posts',
            ],
        ],
    ]);

    $authorData = CollectionData::factory()->create([
        'collection_config_id' => $authorConfig->id,
        'payload' => [
            'uuid' => 'author-uuid-4',
            'name' => 'Jane Doe',
            'posts' => [],
        ],
    ]);

    $postData = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => [
            'uuid' => 'post-uuid-4',
            'title' => 'Post by Jane',
        ],
    ]);

    $payload = $postData->payload;
    $payload['author'] = 'author-uuid-4';
    $postData->payload = $payload;
    $postData->save();

    $authorData->refresh();

    expect($authorData->payload['posts'])->toContain('post-uuid-4');
});
