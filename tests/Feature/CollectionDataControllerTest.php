<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = \A21ns1g4ts\FilamentCollections\Tests\Fixtures\User::factory()->create();

    $this->token = $this->user->createToken('token', ['*']);
});

it('can fetch collection data', function () {
    $config = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
            ['name' => 'published_at', 'type' => 'date'],
        ]
    ]);

    CollectionData::factory()->count(5)->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'Post 1', 'published_at' => '2025-07-31']
    ]);

    $this->getJson("/api/collections/{$config->key}")
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('can filter by text field', function () {
    $config = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
        ]
    ]);

    CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'Hello World']
    ]);

    CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'Another Post']
    ]);

    $this->getJson("/api/collections/{$config->key}?filters[title]=Hello World")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.payload.title', 'Hello World');
});

it('can search by text field', function () {
    $config = CollectionConfig::factory()->create([
        'key' => 'posts',
        'schema' => [
            ['name' => 'title', 'type' => 'text'],
        ]
    ]);

    CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'Hello World']
    ]);

    CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'Another Post']
    ]);

    $this->getJson("/api/collections/{$config->key}?search[title]=Hello")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.payload.title', 'Hello World');
});

it('can create a new record', function () {
    $config = CollectionConfig::factory()->create([
        'key' => 'products',
        'schema' => [
            ['name' => 'name', 'type' => 'text', 'required' => true],
            ['name' => 'price', 'type' => 'number', 'required' => true],
        ]
    ]);

    $payload = [
        'payload' => [
            'name' => 'New Product',
            'price' => 99.99
        ]
    ];

    $this->postJson("/api/collections/{$config->key}", $payload)
        ->assertStatus(201)
        ->assertJsonFragment(['name' => 'New Product']);

    $this->assertDatabaseHas('collection_data', [
        'collection_config_id' => $config->id,
        'payload->name' => 'New Product'
    ]);
});

it('can show a record', function () {
    $config = CollectionConfig::factory()->create(['key' => 'pages']);
    $record = CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'About Us', 'uuid' => (string) Str::uuid()]
    ]);

    $this->getJson("/api/collections/{$config->key}/{$record->payload['uuid']}")
        ->assertOk()
        ->assertJsonFragment(['title' => 'About Us']);
});

it('can update a record', function () {
    $config = CollectionConfig::factory()->create(['key' => 'tasks']);
    $record = CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['title' => 'Old Title', 'completed' => false, 'uuid' => (string) Str::uuid()]
    ]);

    $payload = [
        'payload' => [
            'title' => 'New Title',
            'completed' => true
        ]
    ];

    $this->putJson("/api/collections/{$config->key}/{$record->payload['uuid']}", $payload)
        ->assertOk()
        ->assertJsonFragment(['title' => 'New Title']);

    $this->assertDatabaseHas('collection_data', [
        'id' => $record->id,
        'payload->title' => 'New Title'
    ]);
});

it('can delete a record', function () {
    $config = CollectionConfig::factory()->create(['key' => 'users']);
    $record = CollectionData::factory()->create([
        'collection_config_id' => $config->id,
        'payload' => ['uuid' => (string) Str::uuid()]
    ]);

    $this->deleteJson("/api/collections/{$config->key}/{$record->payload['uuid']}")
        ->assertStatus(204);

    $this->assertDatabaseMissing('collection_data', ['id' => $record->id]);
});