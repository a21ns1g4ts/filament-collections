<?php

namespace A21ns1g4ts\FilamentCollections\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionConfig extends Model
{
    use HasFactory;

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

    public function apis()
    {
        return $this->hasMany(CollectionApi::class);
    }
}
