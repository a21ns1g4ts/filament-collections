<?php

namespace A21ns1g4ts\FilamentCollections\Observers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;
use Illuminate\Support\Facades\DB;

class CollectionConfigObserver
{
    public function saving(CollectionConfig $collectionConfig)
    {
        if ($collectionConfig->isDirty('schema')) {
            $originalSchema = $collectionConfig->getOriginal('schema') ?? [];
            $newSchema = $collectionConfig->schema ?? [];

            $this->handleRemovedFields($collectionConfig, $originalSchema, $newSchema);
            $this->syncInverseRelationships($collectionConfig);
        }
    }

    protected function handleRemovedFields(CollectionConfig $collectionConfig, array $originalSchema, array $newSchema)
    {
        $newFieldNames = array_map(fn ($field) => $field['name'] ?? null, $newSchema);
        $removedFields = array_filter($originalSchema, fn ($field) => ! in_array($field['name'] ?? null, $newFieldNames));
        $inflector = InflectorFactory::createForLanguage(Language::PORTUGUESE)->build();

        foreach ($removedFields as $field) {
            if (($field['type'] ?? null) !== 'collection') {
                continue;
            }

            // Clean up data from the source collection
            CollectionData::where('collection_config_id', $collectionConfig->id)
                ->where(DB::raw("json_extract(payload, '$." . $field['name'] . "')"), '!=', null)
                ->update(['payload' => DB::raw("json_remove(payload, '$." . $field['name'] . "')")]);

            $targetCollectionKey = $field['target_collection_key'] ?? null;
            if (! $targetCollectionKey) {
                continue;
            }

            $targetConfig = CollectionConfig::where('key', $targetCollectionKey)->first();
            if (! $targetConfig) {
                continue;
            }

            // Determine the inverse relationship name
            $relationshipType = $field['relationship_type'] ?? null;
            $inverseRelationshipName = $field['inverse_relationship_name'] ?? null;
            if (! $inverseRelationshipName) {
                if ($relationshipType === 'belongsTo') {
                    $inverseRelationshipName = $collectionConfig->key;
                } elseif ($relationshipType === 'hasMany') {
                    $inverseRelationshipName = $inflector->singularize($collectionConfig->key);
                }
            }

            if (! $inverseRelationshipName) {
                continue;
            }

            // Clean up data from the target collection
            CollectionData::where('collection_config_id', $targetConfig->id)
                ->where(DB::raw("json_extract(payload, '$." . $inverseRelationshipName . "')"), '!=', null)
                ->update(['payload' => DB::raw("json_remove(payload, '$." . $inverseRelationshipName . "')")]);

            // Remove the inverse field from the target schema
            $targetSchema = $targetConfig->schema ?? [];
            $targetSchema = array_filter($targetSchema, fn ($f) => ($f['name'] ?? null) !== $inverseRelationshipName);
            $targetConfig->schema = array_values($targetSchema);
            $targetConfig->saveQuietly();
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
