<?php

namespace A21ns1g4ts\FilamentCollections\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionConfig extends Model
{
    protected $table = 'collections_config';

    protected $fillable = [
        'key',
        'description',
        'schema',
        'ui_schema',
    ];

    protected $casts = [
        'schema' => 'array',
        'ui_schema' => 'array',
    ];

    public function data(): HasMany
    {
        return $this->hasMany(CollectionData::class, 'collection_config_id');
    }
}
