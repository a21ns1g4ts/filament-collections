<?php

use A21ns1g4ts\FilamentCollections\Models\CollectionApi;
use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = \A21ns1g4ts\FilamentCollections\Tests\Fixtures\User::factory()->create();

    $this->token = $this->user->createToken('token', ['*']);
});

// it('returns an error if no collections are informed and paginate_configs is false', function () {
//     $this->getJson('/api/collections')
//         ->assertStatus(400)
//         ->assertJson(['error' => 'No collections were provided.']);
// });

// it('returns paginated collection configs when paginate_configs is true', function () {
//     CollectionConfig::factory()->count(5)->create();

//     $this->getJson('/api/collections?paginate_configs=true')
//         ->assertOk()
//         ->assertJsonStructure([
//             'configs' => [
//                 'meta' => ['key', 'title', 'limit', 'paginated', 'count', 'total'],
//                 'items' => [],
//                 'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
//             ],
//         ])
//         ->assertJsonCount(5, 'configs.items');
// });

// it('returns a single collection data when requested', function () {
//     $config = CollectionConfig::factory()->create(['key' => 'my_collection']);
//     CollectionData::factory()->count(3)->create(['collection_config_id' => $config->id]);

//     $this->getJson('/api/collections?collections[my_collection][limit]=2')
//         ->assertOk()
//         // ->assertJsonStructure([
//         //     'my_collection' => [
//         //         'meta' => ['key', 'title', 'schema', 'limit', 'paginated', 'count', 'total'],
//         //         'items' => [],
//         //         'pagination' => null,
//         //     ],
//         // ])
//         ->assertJsonCount(2, 'my_collection.items');
// });

it('returns paginated data for a collection when requested', function () {
    $config = CollectionConfig::factory()->create(['key' => 'my_paginated_collection']);
    CollectionData::factory()->count(10)->create(['collection_config_id' => $config->id]);

    $this->getJson('/api/collections?collections[my_paginated_collection][paginated]=true&collections[my_paginated_collection][limit]=5&collections[my_paginated_collection][page]=1')
        ->assertOk()
        ->assertJsonStructure([
            'my_paginated_collection' => [
                'meta' => ['key', 'title', 'schema', 'limit', 'paginated', 'count', 'total'],
                'items' => [],
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ])
        ->assertJsonCount(5, 'my_paginated_collection.items')
        ->assertJsonPath('my_paginated_collection.pagination.total', 10);
});

it('returns an error for a non-existent collection', function () {
    $this->getJson('/api/collections?collections[non_existent_collection][limit]=1')
        ->assertOk() // Still 200 because it's an array of responses
        ->assertJson([
            'non_existent_collection' => ['error' => 'Coleção não encontrada.'],
        ]);
});

// it('stores new collection data with valid payload', function () {
//     $config = CollectionConfig::factory()->create([
//         'key' => 'test_collection',
//         'schema' => [
//             ['name' => 'name', 'type' => 'string', 'required' => true],
//             ['name' => 'age', 'type' => 'number', 'required' => false],
//         ],
//     ]);

//     Sanctum::actingAs($this->user, ['*']);
//     CollectionApi::factory()->create([
//         'personal_access_token_id' => $this->user->createToken('token')->accessToken->id,
//         'collection_config_id' => $config->id,
//         'active' => true,
//     ]);

//     $payload = ['name' => 'John Doe', 'age' => 30];

//     $this->postJson('/api/collections', ['key' => 'test_collection', 'payload' => $payload])
//         ->assertStatus(201)
//         ->assertJson(['message' => 'Registro criado com sucesso.']);

//     $this->assertDatabaseHas('collection_data', [
//         'collection_config_id' => $config->id,
//         'payload->name' => 'John Doe',
//         'payload->age' => 30,
//     ]);
// });

// it('returns 422 if key or payload are missing for store', function () {
//     $this->postJson('/api/collections', [])
//         ->assertStatus(422)
//         ->assertJson(['error' => 'A chave da coleção e o payload são obrigatórios.']);

//     $this->postJson('/api/collections', ['key' => 'test'])
//         ->assertStatus(422)
//         ->assertJson(['error' => 'A chave da coleção e o payload são obrigatórios.']);

//     $this->postJson('/api/collections', ['payload' => ['name' => 'test']])
//         ->assertStatus(422)
//         ->assertJson(['error' => 'A chave da coleção e o payload são obrigatórios.']);
// });

// it('returns 404 if collection config not found for store', function () {
//     Sanctum::actingAs($this->user, ['*']);
//     $this->postJson('/api/collections', ['key' => 'non_existent', 'payload' => ['name' => 'test']])
//         ->assertStatus(404)
//         ->assertJson(['error' => 'Coleção não encontrada.']);
// });

// it('returns 403 if API access is denied for store', function () {
//     $config = CollectionConfig::factory()->create(['key' => 'test_collection']);

//     Sanctum::actingAs($this->user, ['*']);
//     // No CollectionApi record or inactive
//     CollectionApi::factory()->create([
//         'personal_access_token_id' => $this->user->currentAccessToken()->id,
//         'collection_config_id' => $config->id,
//         'active' => false, // Inactive API
//     ]);

//     $this->postJson('/api/collections', ['key' => 'test_collection', 'payload' => ['name' => 'test']])
//         ->assertStatus(403)
//         ->assertJson(['error' => 'Acesso negado.']);
// });

// it('validates payload based on schema for store', function () {
//     $config = CollectionConfig::factory()->create([
//         'key' => 'validation_collection',
//         'schema' => [
//             ['name' => 'required_field', 'type' => 'string', 'required' => true],
//             ['name' => 'numeric_field', 'type' => 'number', 'required' => false],
//             ['name' => 'unique_field', 'type' => 'string', 'unique' => true],
//         ],
//     ]);

//     Sanctum::actingAs($this->user, ['*']);
//     CollectionApi::factory()->create([
//         'personal_access_token_id' => $this->user->currentAccessToken()->id,
//         'collection_config_id' => $config->id,
//         'active' => true,
//     ]);

//     // Test required field validation
//     $this->postJson('/api/collections', ['key' => 'validation_collection', 'payload' => ['numeric_field' => 123]])
//         ->assertStatus(422)
//         ->assertJsonValidationErrors('payload.required_field');

//     // Test numeric field validation
//     $this->postJson('/api/collections', ['key' => 'validation_collection', 'payload' => ['required_field' => 'abc', 'numeric_field' => 'not_a_number']])
//         ->assertStatus(422)
//         ->assertJsonValidationErrors('payload.numeric_field');

//     // Test unique field validation
//     $payload1 = ['required_field' => 'first', 'unique_field' => 'unique_value'];
//     $this->postJson('/api/collections', ['key' => 'validation_collection', 'payload' => $payload1])
//         ->assertStatus(201);

//     $payload2 = ['required_field' => 'second', 'unique_field' => 'unique_value'];
//     $this->postJson('/api/collections', ['key' => 'validation_collection', 'payload' => $payload2])
//         ->assertStatus(422)
//         ->assertJsonValidationErrors('payload.unique_field');
// });
