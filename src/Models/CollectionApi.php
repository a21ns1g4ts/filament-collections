<?php

namespace A21ns1g4ts\FilamentCollections\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

class CollectionApi extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_config_id',
        'personal_access_token_id',
        'name',
        'active',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(CollectionConfig::class);
    }

    public function token()
    {
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }
}
