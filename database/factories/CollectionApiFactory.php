<?php

namespace A21ns1g4ts\FilamentCollections\Database\Factories;

use A21ns1g4ts\FilamentCollections\Models\CollectionApi;
use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Sanctum\PersonalAccessToken;

class CollectionApiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CollectionApi::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'collection_config_id' => CollectionConfig::factory(),
            'name' => $this->faker->unique()->words(2, true) . ' API',
            'active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the API is inactive.
     */
    public function inactive(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
