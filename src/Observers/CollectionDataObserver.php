<?php

namespace A21ns1g4ts\FilamentCollections\Observers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Support\Arr;

class CollectionDataObserver
{
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
