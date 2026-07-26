<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

it('can resolve belongsTo relationships dynamically', function () {
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

    // Act: Access the dynamic relationship
    $relatedAuthor = $postData->author;

    // Assert
    expect($relatedAuthor)->toBeInstanceOf(CollectionData::class)
        ->and($relatedAuthor->id)->toBe($authorData->id)
        ->and($relatedAuthor->payload['name'] ?? null)->toBe('John Doe');
});

it('can resolve hasMany relationships dynamically', function () {
    // Arrange: Create collection configurations
    $authorConfig = CollectionConfig::factory()->create([
        'key' => 'authors_with_posts',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
            [
                'name' => 'posts',
                'type' => 'collection',
                'relationship_type' => 'hasMany',
                'target_collection_key' => 'posts_of_author',
                'foreign_key_on_target' => 'author_uuid',
            ],
        ],
    ]);

    $postConfig = CollectionConfig::factory()->create([
        'key' => 'posts_of_author',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
            ['name' => 'author_uuid', 'type' => 'text'],
        ],
    ]);

    // Arrange: Create data
    $authorData = CollectionData::factory()->create([
        'collection_config_id' => $authorConfig->id,
        'payload' => [
            'uuid' => 'author-uuid-2',
            'name' => 'Jane Doe',
        ],
    ]);

    $post1 = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => [
            'uuid' => 'post-uuid-3',
            'title' => 'First post by Jane',
            'author_uuid' => 'author-uuid-2',
        ],
    ]);

    $post2 = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => [
            'uuid' => 'post-uuid-4',
            'title' => 'Second post by Jane',
            'author_uuid' => 'author-uuid-2',
        ],
    ]);

    // Act: Access the dynamic relationship
    $relatedPosts = $authorData->posts;

    // Assert
    expect($relatedPosts)->toBeInstanceOf(Collection::class)
        ->and($relatedPosts->count())->toBe(2)
        ->and($relatedPosts->pluck('payload.title'))->toContain('First post by Jane', 'Second post by Jane');
});

it('can resolve hasOne relationships dynamically', function () {
    // Arrange: Create collection configurations
    $userConfig = CollectionConfig::factory()->create([
        'key' => 'users',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
            [
                'name' => 'profile',
                'type' => 'collection',
                'relationship_type' => 'hasOne',
                'target_collection_key' => 'profiles',
                'foreign_key_on_target' => 'user_uuid',
            ],
        ],
    ]);

    $profileConfig = CollectionConfig::factory()->create([
        'key' => 'profiles',
        'schema' => [
            ['name' => 'bio', 'type' => 'text'],
            ['name' => 'user_uuid', 'type' => 'text'],
        ],
    ]);

    // Arrange: Create data
    $userData = CollectionData::factory()->create([
        'collection_config_id' => $userConfig->id,
        'payload' => [
            'uuid' => 'user-uuid-1',
            'name' => 'John Profile',
        ],
    ]);

    $profileData = CollectionData::factory()->create([
        'collection_config_id' => $profileConfig->id,
        'payload' => [
            'uuid' => 'profile-uuid-1',
            'bio' => 'A short bio',
            'user_uuid' => 'user-uuid-1',
        ],
    ]);

    // Act: Access the dynamic relationship
    $profile = $userData->profile;

    // Assert
    expect($profile)->toBeInstanceOf(CollectionData::class)
        ->and($profile->id)->toBe($profileData->id)
        ->and($profile->payload['bio'])->toBe('A short bio');
});

it('can resolve belongsToMany relationships dynamically', function () {
    // Arrange: Create collection configurations
    $reviewerConfig = CollectionConfig::factory()->create([
        'key' => 'reviewers',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
        ],
    ]);

    $postConfig = CollectionConfig::factory()->create([
        'key' => 'posts_with_reviewers',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
            [
                'name' => 'reviewers',
                'type' => 'collection',
                'relationship_type' => 'belongsToMany',
                'target_collection_key' => 'reviewers',
            ],
        ],
    ]);

    // Arrange: Create data
    $reviewer1 = CollectionData::factory()->create([
        'collection_config_id' => $reviewerConfig->id,
        'payload' => ['uuid' => 'reviewer-uuid-1', 'name' => 'Jane Smith'],
    ]);

    $reviewer2 = CollectionData::factory()->create([
        'collection_config_id' => $reviewerConfig->id,
        'payload' => ['uuid' => 'reviewer-uuid-2', 'name' => 'Peter Jones'],
    ]);

    $postData = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => [
            'uuid' => 'post-uuid-2',
            'title' => 'Post needing review',
            'reviewers' => ['reviewer-uuid-1', 'reviewer-uuid-2'],
        ],
    ]);

    // Act: Access the dynamic relationship
    $relatedReviewers = $postData->reviewers;

    // Assert
    expect($relatedReviewers)->toBeInstanceOf(Collection::class)
        ->and($relatedReviewers->count())->toBe(2)
        ->and($relatedReviewers->pluck('payload.name'))->toContain('Jane Smith', 'Peter Jones');
});

it('returns null for missing target configuration key', function () {
    $config = CollectionConfig::factory()->create([
        'key' => 'broken',
        'schema' => [
            [
                'name' => 'something',
                'type' => 'collection',
                'relationship_type' => 'belongsTo',
                'target_collection_key' => 'non-existent',
            ],
        ],
    ]);

    $data = CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['something' => 'uuid'],
    ]);

    expect($data->something)->toBeNull();
});

it('returns null for missing relationship payload value', function () {
    $authorConfig = CollectionConfig::factory()->create(['key' => 'author_missing']);
    $config = CollectionConfig::factory()->create([
        'key' => 'posts_missing',
        'schema' => [
            [
                'name' => 'author',
                'type' => 'collection',
                'relationship_type' => 'belongsTo',
                'target_collection_key' => 'author_missing',
            ],
        ],
    ]);

    $data = CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => [], // Author is missing from payload
    ]);

    expect($data->author)->toBeNull();
});

it('returns parent attribute for unknown property', function () {
    $config = CollectionConfig::factory()->create(['key' => 'test']);
    $data = CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'Test'],
    ]);

    expect($data->non_existent_property)->toBeNull();
});

it('database has collection_configs table', function () {
    expect(Schema::hasTable('collection_configs'))->toBeTrue();
});
