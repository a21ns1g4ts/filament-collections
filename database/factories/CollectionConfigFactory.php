<?php

namespace A21ns1g4ts\FilamentCollections\Database\Factories;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CollectionConfig::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2),
            'description' => $this->faker->sentence(),
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string'],
                    'content' => ['type' => 'string'],
                ],
                'required' => ['title'],
            ],
            'ui_schema' => [
                'title' => [
                    'ui:autofocus' => true,
                    'ui:emptyValue' => '',
                ],
                'content' => [
                    'ui:widget' => 'textarea',
                ],
            ],
        ];
    }

    /**
     * Indicate that the collection config has a simple schema.
     */
    public function simpleSchema(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                    ],
                ],
                'ui_schema' => [],
            ];
        });
    }
}
