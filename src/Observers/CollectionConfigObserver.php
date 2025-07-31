<?php

namespace A21ns1g4ts\FilamentCollections\Observers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;

class CollectionConfigObserver
{
    public function saving(CollectionConfig $collectionConfig)
    {
        if ($collectionConfig->isDirty('schema')) {
            $this->syncInverseRelationships($collectionConfig);
        }
    }

    protected function syncInverseRelationships(CollectionConfig $collectionConfig)
    {
        $schema = $collectionConfig->schema ?? [];
        $inflector = InflectorFactory::createForLanguage(Language::PORTUGUESE)->build();

        foreach ($schema as $field) {
            if (($field['type'] ?? null) !== 'collection' || empty($field['target_collection_key'])) {
                continue;
            }

            $targetConfig = CollectionConfig::where('key', $field['target_collection_key'])->first();
            if (! $targetConfig) {
                continue;
            }

            $relationshipType = $field['relationship_type'] ?? null;
            $inverseRelationshipName = $field['inverse_relationship_name'] ?? null;

            if ($relationshipType === 'belongsTo') {
                $inverseName = $inverseRelationshipName ?? $collectionConfig->key;
                $this->addOrUpdateInverseRelationship($targetConfig, $inverseName, 'hasMany', $collectionConfig->key, $field['name']);
            } elseif ($relationshipType === 'hasMany') {
                $inverseName = $inverseRelationshipName ?? $inflector->singularize($collectionConfig->key);
                $this->addOrUpdateInverseRelationship($targetConfig, $inverseName, 'belongsTo', $collectionConfig->key, $field['name']);
            }
        }
    }

    protected function addOrUpdateInverseRelationship(CollectionConfig $targetConfig, string $inverseName, string $inverseType, string $sourceKey, string $sourceFieldName)
    {
        $targetSchema = $targetConfig->schema ?? [];
        $inverseFieldExists = false;

        foreach ($targetSchema as $key => $targetField) {
            if (($targetField['name'] ?? null) === $inverseName) {
                $targetSchema[$key]['inverse_relationship_name'] = $sourceFieldName;
                $inverseFieldExists = true;
                break;
            }
        }

        if (! $inverseFieldExists) {
            $targetSchema[] = [
                'name' => $inverseName,
                'type' => 'collection',
                'relationship_type' => $inverseType,
                'target_collection_key' => $sourceKey,
                'inverse_relationship_name' => $sourceFieldName,
            ];
        }

        if ($targetConfig->schema !== $targetSchema) {
            $targetConfig->schema = $targetSchema;
            $targetConfig->saveQuietly();
        }
    }
}
