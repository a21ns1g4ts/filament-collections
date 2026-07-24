<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

it('automatically generates a UUID for collection data item if missing', function () {
    $config = CollectionConfig::factory()->create([
        'key' => 'test_collection',
        'schema' => [
            ['name' => 'name', 'type' => 'text'],
        ],
    ]);

    $data = new CollectionData;
    $data->collection_config_id = $config->id;
    $data->payload = ['name' => 'Test Item'];
    $data->save();

    $data->refresh();
    expect($data->payload)->toHaveKey('uuid');
    expect(Str::isUuid($data->payload['uuid']))->toBeTrue();
});

it('does not overwrite existing UUID on saving collection data', function () {
    $config = CollectionConfig::factory()->create([
        'key' => 'test_collection',
    ]);

    $existingUuid = (string) Str::uuid();
    $data = CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['uuid' => $existingUuid, 'name' => 'Test Item'],
    ]);

    $data->refresh();
    expect($data->payload['uuid'])->toBe($existingUuid);

    $payload = $data->payload;
    $payload['name'] = 'Updated Name';
    $data->payload = $payload;
    $data->save();

    $data->refresh();
    expect($data->payload['uuid'])->toBe($existingUuid);
});

it('automatically generates foreign_key_on_target for hasMany relationships when empty', function () {
    $authorsConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [],
    ]);

    $postsConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [],
    ]);

    // Simulating adding a hasMany relationship without specifying foreign_key_on_target
    $authorsConfig->schema = [
        [
            'name' => 'posts',
            'type' => 'collection',
            'relationship_type' => 'hasMany',
            'target_collection_key' => 'posts',
        ],
    ];
    $authorsConfig->save();

    $authorsConfig->refresh();
    $field = $authorsConfig->schema[0];

    // authors singular + _uuid
    expect($field['foreign_key_on_target'])->toBe('author_uuid');

    $postsConfig->refresh();
    // The inverse relationship should have been created with the auto-generated name
    $inverseField = collect($postsConfig->schema)->firstWhere('name', 'author_uuid');
    expect($inverseField)->not->toBeNull();
    expect($inverseField['relationship_type'])->toBe('belongsTo');
    expect($inverseField['target_collection_key'])->toBe('authors');
});

it('automatically generates foreign_key_on_target for hasOne relationships when empty', function () {
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

    $userConfig->refresh();
    $field = $userConfig->schema[0];

    expect($field['foreign_key_on_target'])->toBe('user_uuid');

    $profileConfig->refresh();
    $inverseField = collect($profileConfig->schema)->firstWhere('name', 'user_uuid');
    expect($inverseField)->not->toBeNull();
    expect($inverseField['relationship_type'])->toBe('hasOne');
});

it('restricts deletion if related records exist and on_delete is restrict', function () {
    $authorsConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
            ['name' => 'posts', 'type' => 'collection', 'relationship_type' => 'hasMany', 'target_collection_key' => 'posts', 'on_delete' => 'restrict'],
        ],
    ]);

    $postsConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'author', 'type' => 'collection', 'relationship_type' => 'belongsTo', 'target_collection_key' => 'authors'],
        ],
    ]);

    $author = CollectionData::factory()->create([
        'collection_config_id' => $authorsConfig->id,
        'payload' => ['uuid' => 'author-1'],
    ]);

    $post = CollectionData::factory()->create([
        'collection_config_id' => $postsConfig->id,
        'payload' => ['uuid' => 'post-1', 'author' => 'author-1'],
    ]);

    $this->expectException(ValidationException::class);
    $author->delete();
});

it('cascades deletion if on_delete is cascade', function () {
    $authorsConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
            ['name' => 'posts', 'type' => 'collection', 'relationship_type' => 'hasMany', 'target_collection_key' => 'posts', 'on_delete' => 'cascade'],
        ],
    ]);

    $postsConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'author', 'type' => 'collection', 'relationship_type' => 'belongsTo', 'target_collection_key' => 'authors', 'on_delete' => 'cascade'],
        ],
    ]);

    $author = CollectionData::factory()->create([
        'collection_config_id' => $authorsConfig->id,
        'payload' => ['uuid' => 'author-1'],
    ]);

    $post = CollectionData::factory()->create([
        'collection_config_id' => $postsConfig->id,
        'payload' => ['uuid' => 'post-1', 'author' => 'author-1'],
    ]);

    $author->delete();

    expect(CollectionData::where('id', $post->id)->exists())->toBeFalse();
});

it('sets null on related records if on_delete is set_null', function () {
    $authorsConfig = CollectionConfig::factory()->create([
        'key' => 'authors',
        'schema' => [
            ['name' => 'posts', 'type' => 'collection', 'relationship_type' => 'hasMany', 'target_collection_key' => 'posts', 'on_delete' => 'set_null'],
        ],
    ]);

    $postsConfig = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'author', 'type' => 'collection', 'relationship_type' => 'belongsTo', 'target_collection_key' => 'authors', 'on_delete' => 'set_null'],
        ],
    ]);

    $author = CollectionData::factory()->create([
        'collection_config_id' => $authorsConfig->id,
        'payload' => ['uuid' => 'author-1'],
    ]);

    $post = CollectionData::factory()->create([
        'collection_config_id' => $postsConfig->id,
        'payload' => ['uuid' => 'post-1', 'author' => 'author-1'],
    ]);

    $author->delete();

    $post->refresh();
    expect($post->payload)->not->toHaveKey('author');
});
