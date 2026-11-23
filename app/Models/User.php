<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = ['full_name', 'email', 'password', 'avatar'];
    protected $hidden = ['password', 'remember_token'];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'sold_by');
    }
}
