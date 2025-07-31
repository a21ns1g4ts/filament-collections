<?php

namespace A21ns1g4ts\FilamentCollections\Observers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Support\Arr;

class CollectionDataObserver
{
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
            if (empty($foreignKeyName) || empty($targetCollectionKey) || empty($inverseRelationshipName)) {
                continue;
            }

            $relatedUuids = Arr::get($collectionData->payload, $foreignKeyName, []);
            if (!is_array($relatedUuids)) {
                $relatedUuids = [$relatedUuids];
            }

            $targetConfig = CollectionConfig::where('key', $targetCollectionKey)->first();
            if (!$targetConfig) {
                continue;
            }

            CollectionData::where('collection_config_id', $targetConfig->id)
                ->whereJsonContains("payload->{$inverseRelationshipName}", $uuid)
                ->whereNotIn('payload->uuid', $relatedUuids)
                ->each(function (CollectionData $item) use ($inverseRelationshipName, $uuid) {
                    $payload = $item->payload;
                    $currentInverseUuids = Arr::get($payload, $inverseRelationshipName, []);
                    if (is_array($currentInverseUuids)) {
                        $filteredUuids = array_values(array_filter($currentInverseUuids, fn($currentUuid) => $currentUuid !== $uuid));
                        Arr::set($payload, $inverseRelationshipName, $filteredUuids);
                        $item->withoutEvents(fn() => $item->update(['payload' => $payload]));
                    }
                });

            CollectionData::where('collection_config_id', $targetConfig->id)
                ->whereIn('payload->uuid', $relatedUuids)
                ->each(function (CollectionData $item) use ($inverseRelationshipName, $uuid) {
                    $payload = $item->payload;
                    $currentInverseUuids = Arr::get($payload, $inverseRelationshipName, []);
                    if (!is_array($currentInverseUuids)) {
                        $currentInverseUuids = [];
                    }
                    if (!in_array($uuid, $currentInverseUuids)) {
                        $currentInverseUuids[] = $uuid;
                        Arr::set($payload, $inverseRelationshipName, $currentInverseUuids);
                        $item->withoutEvents(fn() => $item->update(['payload' => $payload]));
                    }
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
