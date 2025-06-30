<?php

namespace A21ns1g4ts\FilamentCollections\Tests\Fixtures;

use A21ns1g4ts\FilamentCollections\Tests\database\factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
