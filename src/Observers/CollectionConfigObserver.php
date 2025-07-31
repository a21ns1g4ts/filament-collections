<?php

namespace A21ns1g4ts\FilamentCollections\Observers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Doctrine\Inflector\InflectorFactory;

class CollectionConfigObserver
{
    protected static $inflector;

    protected static $language = 'portuguese';

    public function saved(CollectionConfig $collectionConfig)
    {
        $this->syncInverseRelationships($collectionConfig);
    }

    protected function syncInverseRelationships(CollectionConfig $collectionConfig)
    {
        $schema = $collectionConfig->schema ?? [];

        foreach ($schema as $field) {
            if (($field['type'] ?? null) !== 'collection') {
                continue;
            }

            $relationshipType = $field['relationship_type'] ?? null;
            $targetCollectionKey = $field['target_collection_key'] ?? null;

            if (! $relationshipType || ! $targetCollectionKey) {
                continue;
            }

            $targetConfig = CollectionConfig::where('key', $targetCollectionKey)->first();
            if (! $targetConfig) {
                continue;
            }

            if ($relationshipType === 'belongsTo') {
                $this->addHasManyToTarget($collectionConfig, $targetConfig, $field['name']);
            } elseif ($relationshipType === 'hasMany') {
                $this->addBelongsToToTarget($collectionConfig, $targetConfig);
            }
        }
    }

    protected function addHasManyToTarget(CollectionConfig $sourceConfig, CollectionConfig $targetConfig, string $foreignKey)
    {
        $targetSchema = $targetConfig->schema ?? [];
        $inverseRelationshipName = $sourceConfig->key;

        $inverseRelationshipExists = collect($targetSchema)->contains(function ($field) use ($inverseRelationshipName) {
            return ($field['name'] ?? null) === $inverseRelationshipName && ($field['relationship_type'] ?? null) === 'hasMany';
        });

        if (! $inverseRelationshipExists) {
            $targetSchema[] = [
                'name' => $inverseRelationshipName,
                'type' => 'collection',
                'relationship_type' => 'hasMany',
                'target_collection_key' => $sourceConfig->key,
                'foreign_key_on_target' => $foreignKey,
            ];

            $targetConfig->schema = $targetSchema;
            $targetConfig->saveQuietly();
        }
    }

    protected function addBelongsToToTarget(CollectionConfig $sourceConfig, CollectionConfig $targetConfig)
    {
        $targetSchema = $targetConfig->schema ?? [];
        $expectedInverseFieldName = self::inflector()->singularize($sourceConfig->key);

        $inverseRelationshipExists = collect($targetSchema)->contains(function ($field) use ($expectedInverseFieldName) {
            return ($field['name'] ?? null) === $expectedInverseFieldName && ($field['relationship_type'] ?? null) === 'belongsTo';
        });


        if (! $inverseRelationshipExists) {
            $targetSchema[] = [
                'name' => $expectedInverseFieldName,
                'type' => 'collection',
                'relationship_type' => 'belongsTo',
                'target_collection_key' => $sourceConfig->key,
                'foreign_key_on_target' => $expectedInverseFieldName,
            ];

            $targetConfig->schema = $targetSchema;
            $targetConfig->saveQuietly();
        }
    }

    public static function inflector()
    {
        if (is_null(static::$inflector)) {
            static::$inflector = InflectorFactory::createForLanguage(static::$language)->build();
        }

        return static::$inflector;
    }
}
