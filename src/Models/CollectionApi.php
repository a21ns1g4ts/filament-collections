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
        'personal_access_token_id',
        'name',
        'active',
    ];

    public function configs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CollectionConfig::class, 'collection_api_configs');
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CollectionGroup::class, 'collection_api_groups');
    }

    public function token()
    {
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }
}
