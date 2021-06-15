<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $table = 'addresses';

    protected $fillable = ['user_id', 'province', 'city', 'village', 'street', 'postal_code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
