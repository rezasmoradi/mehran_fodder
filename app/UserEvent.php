<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    protected $table = 'users_events';

    protected $fillable = ['user_id', 'event_id', 'read_status'];
}
