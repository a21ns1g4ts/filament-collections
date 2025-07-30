<?php

namespace A21ns1g4ts\FilamentCollections\Models\Traits;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;

trait HasDynamicRelationships
{
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes) || array_key_exists($key, $this->relations)) {
            return parent::__get($key);
        }

        if (! $this->relationLoaded('config')) {
            $this->load('config');
        }
        $config = $this->getRelation('config');

        if ($config && $config->schema) {
            foreach ($config->schema as $field) {
                if (($field['type'] ?? null) === 'collection' && ($field['name'] ?? null) === $key) {
                    $relationshipType = $field['relationship_type'] ?? 'belongsTo';
                    $relatedModel = self::class;
                    $targetCollectionKey = $field['target_collection_key'] ?? null;

                    if (! $targetCollectionKey) {
                        continue;
                    }

                    $targetConfig = CollectionConfig::where('key', $targetCollectionKey)->first();

                    if (! $targetConfig) {
                        continue;
                    }

                    if ($relationshipType === 'belongsTo') {
                        $foreignKeyValue = $this->payload[$field['name']] ?? null;
                        if (! $foreignKeyValue) {
                            return null;
                        }

                        return $relatedModel::where('collection_config_id', $targetConfig->id)
                            ->where('payload->uuid', $foreignKeyValue)
                            ->first();
                    }

                    if ($relationshipType === 'hasMany') {
                        $foreignKeyOnTarget = $field['foreign_key_on_target'] ?? null;
                        if (! $foreignKeyOnTarget) {
                            continue;
                        }
                        $uuid = $this->payload['uuid'] ?? null;
                        if (! $uuid) {
                            return collect();
                        }

                        return $relatedModel::where('collection_config_id', $targetConfig->id)
                            ->where("payload->{$foreignKeyOnTarget}", $uuid)
                            ->get();
                    }

                    if ($relationshipType === 'belongsToMany') {
                        $foreignKeyValues = $this->payload[$field['name']] ?? [];
                        if (! is_array($foreignKeyValues) || empty($foreignKeyValues)) {
                            return collect();
                        }

                        return $relatedModel::where('collection_config_id', $targetConfig->id)
                            ->whereIn('payload->uuid', $foreignKeyValues)
                            ->get();
                    }
                }
            }
        }

        return parent::__get($key);
    }
}
