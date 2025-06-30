<?php

namespace A21ns1g4ts\FilamentCollections\Database\Factories;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CollectionData::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'collection_config_id' => CollectionConfig::factory(),
            'payload' => [
                'title' => $this->faker->sentence(3),
                'content' => $this->faker->paragraph(2),
                'status' => $this->faker->randomElement(['published', 'draft', 'archived']),
                'tags' => $this->faker->words(3),
                'views' => $this->faker->numberBetween(0, 1000),
            ],
        ];
    }

    /**
     * Indicate that the collection data is a draft.
     */
    public function draft(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'payload' => array_merge($attributes['payload'] ?? [], ['status' => 'draft']),
        ]);
    }

    /**
     * Indicate that the collection data is published.
     */
    public function published(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'payload' => array_merge($attributes['payload'] ?? [], ['status' => 'published']),
        ]);
    }
}
