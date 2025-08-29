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

it('updates inverse hasMany relationship when belongsTo is updated', function () {
    $authorConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
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
            [
                'name' => 'author',
                'type' => 'collection',
                'relationship_type' => 'belongsTo',
                'target_collection_key' => 'authors',
                'inverse_relationship_name' => 'posts',
            ],
        ],
    ]);

    $author1 = CollectionData::factory()->create([
        'collection_config_id' => $authorConfig->id,
        'payload' => ['uuid' => 'author-1', 'posts' => []],
    ]);

    $author2 = CollectionData::factory()->create([
        'collection_config_id' => $authorConfig->id,
        'payload' => ['uuid' => 'author-2', 'posts' => []],
    ]);

    $post = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => ['uuid' => 'post-1'],
    ]);

    // Act: Assign post to author1
    $payload = $post->payload;
    $payload['author'] = 'author-1';
    $post->payload = $payload;
    $post->save();

    // Assert: Check if inverse relationship is updated
    $author1->refresh();
    expect($author1->payload['posts'])->toContain('post-1');

    // Act: Re-assign post to author2
    $payload = $post->payload;
    $payload['author'] = 'author-2';
    $post->payload = $payload;
    $post->save();

    // Assert: Check if old relationship is removed and new one is added
    $author1->refresh();
    $author2->refresh();
    expect($author1->payload['posts'])->toBeEmpty();
    expect($author2->payload['posts'])->toContain('post-1');

    // Act: Remove assignment from post
    $payload = $post->payload;
    $payload['author'] = null;
    $post->payload = $payload;
    $post->save();

    // Assert: Check if relationship is removed from author2
    $author2->refresh();
    expect($author2->payload['posts'])->toBeEmpty();
});

it('it updates inverse hasOne relationship when hasOne is updated', function () {
    // Arrange
    $userConfig = CollectionConfig::factory()->create([
        'key' => 'users',
        'schema' => [
            [
                'name' => 'profile',
                'type' => 'collection',
                'relationship_type' => 'hasOne',
                'target_collection_key' => 'profiles',
                'inverse_relationship_name' => 'user',
            ],
        ],
    ]);

    $profileConfig = CollectionConfig::factory()->create([
        'key' => 'profiles',
        'schema' => [
            [
                'name' => 'user',
                'type' => 'collection',
                'relationship_type' => 'hasOne',
                'target_collection_key' => 'users',
                'inverse_relationship_name' => 'profile',
            ],
        ],
    ]);

    $user1 = CollectionData::factory()->create([
        'collection_config_id' => $userConfig->id,
        'payload' => ['uuid' => 'user-1'],
    ]);

    $user2 = CollectionData::factory()->create([
        'collection_config_id' => $userConfig->id,
        'payload' => ['uuid' => 'user-2'],
    ]);

    $profile = CollectionData::factory()->create([
        'collection_config_id' => $profileConfig->id,
        'payload' => ['uuid' => 'profile-1'],
    ]);

    // Act: Assign profile to user1
    $payload = $user1->payload;
    $payload['profile'] = 'profile-1';
    $user1->payload = $payload;
    $user1->save();

    // Assert: Check if inverse relationship is updated
    $profile->refresh();
    expect($profile->payload['user'])->toBe('user-1');

    // Act: Re-assign profile to user2
    $payload = $user1->payload;
    $payload['profile'] = null;
    $user1->payload = $payload;
    $user1->save();

    $payload = $user2->payload;
    $payload['profile'] = 'profile-1';
    $user2->payload = $payload;
    $user2->save();

    // Assert: Check if old relationship is removed and new one is added
    $profile->refresh();
    expect($profile->payload['user'])->toBe('user-2');

    // Act: Remove assignment from user2
    $payload = $user2->payload;
    $payload['profile'] = null;
    $user2->payload = $payload;
    $user2->save();

    // Assert: Check if relationship is removed from profile
    $profile->refresh();
    expect($profile->payload)->not->toHaveKey('user');
});

it('it updates inverse belongsTo relationship when hasMany is updated', function () {
    // Arrange
    $authorConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
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
            [
                'name' => 'author',
                'type' => 'collection',
                'relationship_type' => 'belongsTo',
                'target_collection_key' => 'authors',
                'inverse_relationship_name' => 'posts',
            ],
        ],
    ]);

    $author = CollectionData::factory()->create([
        'collection_config_id' => $authorConfig->id,
        'payload' => ['uuid' => 'author-1', 'posts' => []],
    ]);

    $post1 = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => ['uuid' => 'post-1'],
    ]);

    $post2 = CollectionData::factory()->create([
        'collection_config_id' => $postConfig->id,
        'payload' => ['uuid' => 'post-2'],
    ]);

    // Act: Assign post1 to author
    $payload = $author->payload;
    $payload['posts'] = ['post-1'];
    $author->payload = $payload;
    $author->save();

    // Assert: Check if inverse relationship is updated in post1
    $post1->refresh();
    expect($post1->payload['author'])->toBe('author-1');

    // Act: Assign post2 to author (add to existing)
    $payload = $author->payload;
    $payload['posts'] = ['post-1', 'post-2'];
    $author->payload = $payload;
    $author->save();

    // Assert: Check if inverse relationship is updated in post2
    $post2->refresh();
    expect($post2->payload['author'])->toBe('author-1');

    // Act: Remove post1 from author
    $payload = $author->payload;
    $payload['posts'] = ['post-2'];
    $author->payload = $payload;
    $author->save();

    // Assert: Check if inverse relationship is removed from post1
    $post1->refresh();
    expect($post1->payload)->not->toHaveKey('author');
    expect($post2->payload['author'])->toBe('author-1');
});
