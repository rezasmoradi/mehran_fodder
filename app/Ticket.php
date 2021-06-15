<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'user_id', 'user_ip', 'request_text', 'response_text', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toArray()
    {
        $data = parent::toArray();
        if (array_key_exists('deleted_at', $this->attributes) && $this->attributes['deleted_at'] !== null) {
            $jalali = to_jalali([
                'created_at' => $this->attributes['created_at'],
                'updated_at' => $this->attributes['updated_at'],
                'deleted_at' => $this->attributes['deleted_at'],
            ]);
        } elseif ((array_key_exists('created_at', $this->attributes) && $this->attributes['created_at'] !== null)) {
            $jalali = to_jalali([
                'created_at' => $this->attributes['created_at'],
                'updated_at' => $this->attributes['updated_at'],
            ]);
        } else {
            return $data;
        }
        $data['created_at'] = $jalali['created_at']->formatDateTime();
        $data['updated_at'] = $jalali['updated_at']->formatDateTime();
        $data['deleted_at'] = !array_key_exists('deleted_at', $jalali) || is_null($jalali['deleted_at']) ? null : $jalali['deleted_at']->formatDateTime();

        return $data;
    }
}
