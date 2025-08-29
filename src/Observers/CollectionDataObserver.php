<?php

namespace A21ns1g4ts\FilamentCollections\Observers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CollectionDataObserver
{
    public function saving(CollectionData $collectionData): void
    {
        if (!$collectionData->relationLoaded('config')) {
            $collectionData->load('config');
        }

        foreach ($collectionData->config->schema as $field) {
            if (Arr::get($field, 'type') !== 'collection' || Arr::get($field, 'relationship_type') !== 'hasOne') {
                continue;
            }

            $foreignKeyName = Arr::get($field, 'name');
            $relatedUuid = Arr::get($collectionData->payload, $foreignKeyName);

            if (empty($relatedUuid)) {
                continue;
            }

            $query = CollectionData::where('collection_config_id', $collectionData->collection_config_id)
                ->where("payload->{$foreignKeyName}", $relatedUuid);

            if ($collectionData->exists) {
                $query->where('id', '!=', $collectionData->id);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    $foreignKeyName => 'This ' . Arr::get($field, 'target_collection_key') . ' is already assigned to another record.',
                ]);
            }
        }
    }

    public function saved(CollectionData $collectionData): void
    {
        if (!$collectionData->relationLoaded('config')) {
            $collectionData->load('config');
        }

        $uuid = Arr::get($collectionData->payload, 'uuid');
        if (empty($uuid)) {
            return;
        }

        foreach ($collectionData->config->schema as $field) {
            if (Arr::get($field, 'type') !== 'collection') {
                continue;
            }

            $foreignKeyName = Arr::get($field, 'name');
            $targetCollectionKey = Arr::get($field, 'target_collection_key');
            $inverseRelationshipName = Arr::get($field, 'inverse_relationship_name');
            $relationshipType = Arr::get($field, 'relationship_type');
            if (empty($foreignKeyName) || empty($targetCollectionKey) || empty($inverseRelationshipName)) {
                continue;
            }

            $targetConfig = CollectionConfig::where('key', $targetCollectionKey)->first();
            if (!$targetConfig) {
                continue;
            }

            $newRelatedUuids = Arr::wrap(Arr::get($collectionData->payload, $foreignKeyName, []));
            $originalRelatedUuids = Arr::wrap(Arr::get($collectionData->getOriginal('payload'), $foreignKeyName, []));

            $uuidsToDetach = array_diff($originalRelatedUuids, $newRelatedUuids);
            $uuidsToAttach = array_diff($newRelatedUuids, $originalRelatedUuids);

            // Handle detachments
            foreach ($uuidsToDetach as $relatedUuid) {
                $this->detachRelated($targetConfig, $inverseRelationshipName, $uuid, $relatedUuid, $relationshipType);
            }

            // Handle attachments
            foreach ($uuidsToAttach as $relatedUuid) {
                $this->attachRelated($targetConfig, $inverseRelationshipName, $uuid, $relatedUuid, $relationshipType);
            }
        }
    }

    protected function detachRelated(CollectionConfig $targetConfig, string $inverseRelationshipName, string $sourceUuid, string $relatedUuid, string $relationshipType): void
    {
        $relatedModel = CollectionData::where('collection_config_id', $targetConfig->id)
            ->where('payload->uuid', $relatedUuid)
            ->first();

        if ($relatedModel) {
            $relatedModel->withoutEvents(function () use ($relatedModel, $inverseRelationshipName, $sourceUuid, $relationshipType) {
                $payload = $relatedModel->payload;
                $inverseValue = Arr::get($payload, $inverseRelationshipName);

                if (in_array($relationshipType, ['belongsTo', 'hasOne'])) {
                    // For belongsTo/hasOne, the inverse is hasMany/hasOne, so remove from array or unset
                    if (is_array($inverseValue)) {
                        $inverseValue = array_values(array_filter($inverseValue, fn($id) => $id !== $sourceUuid));
                        Arr::set($payload, $inverseRelationshipName, $inverseValue);
                    } else {
                        // If it's a single value and matches, unset it
                        if ($inverseValue === $sourceUuid) {
                            unset($payload[$inverseRelationshipName]);
                        }
                    }
                } elseif ($relationshipType === 'hasMany') {
                    // For hasMany, the inverse is belongsTo/hasOne, so unset the single value
                    if ($inverseValue === $sourceUuid) {
                        unset($payload[$inverseRelationshipName]);
                    }
                }
                $relatedModel->update(['payload' => $payload]);
            });
        }
    }

    protected function attachRelated(CollectionConfig $targetConfig, string $inverseRelationshipName, string $sourceUuid, string $relatedUuid, string $relationshipType): void
    {
        $relatedModel = CollectionData::where('collection_config_id', $targetConfig->id)
            ->where('payload->uuid', $relatedUuid)
            ->first();

        if ($relatedModel) {
            $relatedModel->withoutEvents(function () use ($relatedModel, $inverseRelationshipName, $sourceUuid, $relationshipType) {
                $payload = $relatedModel->payload;

                if (in_array($relationshipType, ['belongsTo', 'hasOne'])) {
                    // For belongsTo/hasOne, the inverse is hasMany/hasOne, so add to array or set single value
                    $inverseValue = Arr::get($payload, $inverseRelationshipName);
                    if (is_array($inverseValue)) {
                        if (!in_array($sourceUuid, $inverseValue)) {
                            $inverseValue[] = $sourceUuid;
                            Arr::set($payload, $inverseRelationshipName, $inverseValue);
                        }
                    } else {
                        Arr::set($payload, $inverseRelationshipName, $sourceUuid);
                    }
                } elseif ($relationshipType === 'hasMany') {
                    // For hasMany, the inverse is belongsTo/hasOne, so set the single value
                    Arr::set($payload, $inverseRelationshipName, $sourceUuid);
                }
                $relatedModel->update(['payload' => $payload]);
            });
        }
    }

    public function deleted(CollectionData $collectionData): void
    {
        $deletedUuid = Arr::get($collectionData->payload, 'uuid');
        if (empty($deletedUuid)) {
            return;
        }

        if (!$collectionData->relationLoaded('config')) {
            $collectionData->load('config');
        }

        $deletedCollectionKey = Arr::get($collectionData->config, 'key');
        if (empty($deletedCollectionKey)) {
            return;
        }

        $sourceConfigs = CollectionConfig::where('schema', 'like', '%"target_collection_key":"' . $deletedCollectionKey . '"%')->get();

        foreach ($sourceConfigs as $sourceConfig) {
            foreach ($sourceConfig->schema as $field) {
                if (Arr::get($field, 'type') !== 'collection' || Arr::get($field, 'target_collection_key') !== $deletedCollectionKey) {
                    continue;
                }

                $relationshipType = Arr::get($field, 'relationship_type');
                $foreignKeyName = Arr::get($field, 'name');

                if (empty($foreignKeyName)) {
                    continue;
                }

                if ($relationshipType === 'belongsTo') {
                    CollectionData::where('collection_config_id', $sourceConfig->id)
                        ->where("payload->{$foreignKeyName}", $deletedUuid)
                        ->each(function (CollectionData $item) use ($foreignKeyName) {
                            $payload = $item->payload;
                            unset($payload[$foreignKeyName]);
                            $item->update(['payload' => $payload]);
                        });
                } elseif ($relationshipType === 'hasMany') {
                    CollectionData::where('collection_config_id', $sourceConfig->id)
                        ->whereJsonContains("payload->{$foreignKeyName}", $deletedUuid)
                        ->each(function (CollectionData $item) use ($foreignKeyName, $deletedUuid) {
                            $payload = $item->payload;
                            $uuids = Arr::get($payload, $foreignKeyName, []);

                            if (is_array($uuids)) {
                                $filteredUuids = array_values(array_filter($uuids, fn($uuid) => $uuid !== $deletedUuid));
                                Arr::set($payload, $foreignKeyName, $filteredUuids);
                                $item->update(['payload' => $payload]);
                            }
                        });
                }
            }
        }
    }
}
