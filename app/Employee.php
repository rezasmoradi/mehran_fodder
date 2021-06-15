<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use Notifiable, SoftDeletes;

    protected $table = 'employees';

    protected $fillable = ['user_id', 'employee_code', 'employed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
