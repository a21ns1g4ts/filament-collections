<?php

namespace A21ns1g4ts\FilamentCollections\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasDynamicRelationships
{
    public function __call($method, $parameters)
    {
        $config = $this->relationLoaded('config') ? $this->getRelation('config') : $this->config()->getResults();

        if ($config && $config->schema) {
            foreach ($config->schema as $field) {
                if (($field['type'] ?? null) === 'collection' && ($field['name'] ?? null) === $method) {
                    $relationshipType = $field['relationship_type'] ?? 'belongsTo';
                    $relatedModel = self::class;
                    $foreignKey = $field['name'];

                    if ($relationshipType === 'belongsTo') {
                        return $this->belongsTo($relatedModel, $foreignKey, 'payload->uuid');
                    }

                    if ($relationshipType === 'hasMany') {
                        return $this->hasMany($relatedModel, $foreignKey, 'payload->uuid');
                    }
                }
            }
        }

        return parent::__call($method, $parameters);
    }
}
