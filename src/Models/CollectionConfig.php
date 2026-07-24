<?php

namespace A21ns1g4ts\FilamentCollections\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'description',
        'title_field',
        'schema',
        'ui_schema',
        'collection_group_id',
    ];

    protected $casts = [
        'schema' => 'array',
        'ui_schema' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CollectionGroup::class, 'collection_group_id');
    }

    public function data(): HasMany
    {
        return $this->hasMany(CollectionData::class, 'collection_config_id');
    }

    public function apis(): BelongsToMany
    {
        return $this->belongsToMany(CollectionApi::class, 'collection_api_configs');
    }
}
