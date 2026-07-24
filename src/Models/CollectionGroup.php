<?php

namespace A21ns1g4ts\FilamentCollections\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
    ];

    public function configs(): HasMany
    {
        return $this->hasMany(CollectionConfig::class, 'collection_group_id');
    }

    public function apis(): BelongsToMany
    {
        return $this->belongsToMany(CollectionApi::class, 'collection_api_groups');
    }
}
