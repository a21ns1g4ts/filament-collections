<?php

namespace A21ns1g4ts\FilamentCollections\Observers;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr; // Import Arr helper for safer array manipulation

class CollectionDataObserver
{
    /**
     * Handle the CollectionData "deleted" event.
     */
    public function deleted(CollectionData $collectionData): void
    {
        // Early exit if the deleted item's UUID is not available
        $deletedUuid = Arr::get($collectionData->payload, 'uuid');
        if (empty($deletedUuid)) {
            return;
        }

        // Ensure the 'config' relationship is loaded to prevent N+1 queries
        if (!$collectionData->relationLoaded('config')) {
            $collectionData->load('config');
        }

        // Early exit if the deleted collection's key is not available
        $deletedCollectionKey = Arr::get($collectionData->config, 'key');
        if (empty($deletedCollectionKey)) {
            return;
        }

        // Find all CollectionConfigs that might have relationships pointing to the deleted item's collection.
        // We use 'whereJsonContains' for efficiency and to find configurations
        // where 'target_collection_key' within the 'schema' JSON array matches.
        $sourceConfigs = CollectionConfig::whereJsonContains('schema', ['target_collection_key' => $deletedCollectionKey])->get();

        foreach ($sourceConfigs as $sourceConfig) {
            // Iterate through each field in the source config's schema
            foreach ($sourceConfig->schema as $field) {
                // Check if the field is a 'collection' type and targets the deleted collection
                if (Arr::get($field, 'type') === 'collection' && Arr::get($field, 'target_collection_key') === $deletedCollectionKey) {
                    $relationshipType = Arr::get($field, 'relationship_type');
                    $foreignKeyName = Arr::get($field, 'name');

                    // Skip if foreign key name is not defined for this relationship field
                    if (empty($foreignKeyName)) {
                        continue;
                    }

                    // --- Handle 'belongsTo' relationships ---
                    // If a 'belongsTo' relationship exists, set the foreign key to null.
                    if ($relationshipType === 'belongsTo') {
                        // Retrieve items that reference the deleted UUID.
                        // We use `where("payload->{$foreignKeyName}", $deletedUuid)` for direct JSON key access.
                        CollectionData::where('collection_config_id', $sourceConfig->id)
                            ->where("payload->{$foreignKeyName}", $deletedUuid)
                            ->get()
                            ->each(function (CollectionData $item) use ($foreignKeyName) {
                                $payload = $item->payload;
                                Arr::set($payload, $foreignKeyName, null); // Safely set to null
                                // Update the payload directly using the model's update method,
                                // which handles JSON encoding automatically if `payload` is cast to `array` or `json`.
                                $item->update(['payload' => $payload]);
                            });
                    }

                    // --- Handle 'hasMany' relationships ---
                    // If a 'hasMany' relationship exists, remove the deleted UUID from the array.
                    if ($relationshipType === 'hasMany') {
                        // Retrieve items where the foreign key array contains the deleted UUID.
                        // `whereJsonContains` is appropriate here for checking array elements.
                        CollectionData::where('collection_config_id', $sourceConfig->id)
                            ->whereJsonContains("payload->{$foreignKeyName}", $deletedUuid)
                            ->get()
                            ->each(function (CollectionData $item) use ($foreignKeyName, $deletedUuid) {
                                $payload = $item->payload;
                                $uuids = Arr::get($payload, $foreignKeyName, []);

                                // Ensure it's an array before filtering
                                if (is_array($uuids)) {
                                    // Filter out the deleted UUID
                                    $filteredUuids = array_values(array_filter($uuids, fn($uuid) => $uuid !== $deletedUuid));
                                    Arr::set($payload, $foreignKeyName, $filteredUuids); // Safely update the payload
                                    $item->update(['payload' => $payload]);
                                }
                            });
                    }
                }
            }
        }
    }
}
