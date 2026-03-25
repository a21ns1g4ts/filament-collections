<?php

namespace A21ns1g4ts\FilamentCollections\Support;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionGroup;

class FilamentCollectionsFactory
{
    /**
     * Create a complete blog structure.
     */
    public static function createBlog(): CollectionGroup
    {
        $group = CollectionGroup::firstOrCreate(
            ['key' => 'blog'],
            [
                'name' => 'Blog',
                'description' => 'Estrutura completa de blog com posts, categorias e tags.',
            ]
        );

        // Category
        CollectionConfig::firstOrCreate(
            ['key' => 'categories'],
            [
                'description' => 'Categorias do Blog',
                'collection_group_id' => $group->id,
                'title_field' => 'name',
                'schema' => [
                    ['name' => 'name', 'type' => 'text', 'required' => true],
                    ['name' => 'slug', 'type' => 'text', 'required' => true, 'sluggable' => true, 'slug_source' => 'name'],
                    [
                        'name' => 'posts',
                        'type' => 'collection',
                        'relationship_type' => 'hasMany',
                        'target_collection_key' => 'posts',
                        'inverse_relationship_name' => 'category',
                        'on_delete' => 'restrict'
                    ]
                ],
            ]
        );

        // Tags
        CollectionConfig::firstOrCreate(
            ['key' => 'tags'],
            [
                'description' => 'Tags do Blog',
                'collection_group_id' => $group->id,
                'title_field' => 'name',
                'schema' => [
                    ['name' => 'name', 'type' => 'text', 'required' => true],
                    [
                        'name' => 'posts',
                        'type' => 'collection',
                        'relationship_type' => 'belongsToMany',
                        'target_collection_key' => 'posts',
                        'inverse_relationship_name' => 'tags',
                        'on_delete' => 'restrict'
                    ]
                ],
            ]
        );

        // Posts
        CollectionConfig::firstOrCreate(
            ['key' => 'posts'],
            [
                'description' => 'Posts do Blog',
                'collection_group_id' => $group->id,
                'title_field' => 'title',
                'schema' => [
                    ['name' => 'title', 'type' => 'text', 'required' => true],
                    ['name' => 'slug', 'type' => 'text', 'required' => true, 'sluggable' => true, 'slug_source' => 'title'],
                    ['name' => 'content', 'type' => 'markdown'],
                    [
                        'name' => 'category',
                        'type' => 'collection',
                        'relationship_type' => 'belongsTo',
                        'target_collection_key' => 'categories',
                        'inverse_relationship_name' => 'posts',
                        'on_delete' => 'restrict'
                    ],
                    [
                        'name' => 'tags',
                        'type' => 'collection',
                        'relationship_type' => 'belongsToMany',
                        'target_collection_key' => 'tags',
                        'inverse_relationship_name' => 'posts',
                        'on_delete' => 'restrict'
                    ],
                ],
            ]
        );

        return $group;
    }

    /**
     * Create a basic CMS structure.
     */
    public static function createCMS(): CollectionGroup
    {
        $group = CollectionGroup::firstOrCreate(
            ['key' => 'cms'],
            [
                'name' => 'CMS',
                'description' => 'Sistema de gerenciamento de conteúdo básico com páginas e blocos.',
            ]
        );

        CollectionConfig::firstOrCreate(
            ['key' => 'pages'],
            [
                'description' => 'Páginas do Site',
                'collection_group_id' => $group->id,
                'title_field' => 'title',
                'schema' => [
                    ['name' => 'title', 'type' => 'text', 'required' => true],
                    ['name' => 'slug', 'type' => 'text', 'required' => true],
                    ['name' => 'content', 'type' => 'markdown'],
                ],
            ]
        );

        return $group;
    }
}
