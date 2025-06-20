<?php

namespace A21ns1g4ts\FilamentCollections\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionData extends Model
{
    protected $table = 'collections_data';

    protected $fillable = [
        'collection_config_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Relação inversa com CollectionConfig
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(CollectionConfig::class, 'collection_config_id');
    }
}
