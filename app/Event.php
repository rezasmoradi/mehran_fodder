<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $table = 'events';
    protected $fillable = ['event_title', 'event_content', 'publish_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function view()
    {
        return $this->belongsToMany(UserEvent::class, 'users_events');
    }

    public function toArray()
    {
        if (array_key_exists('publish_date', $this->attributes) && $this->attributes['publish_date'] !== null) {
            $jalali = to_jalali([
                'created_at' => $this->attributes['created_at'],
                'updated_at' => $this->attributes['updated_at'],
                'publish_date' => $this->attributes['publish_date'],
            ]);
        }
        elseif (array_key_exists('deleted_at', $this->attributes) && $this->attributes['deleted_at'] !== null) {
            $jalali = to_jalali([
                'created_at' => $this->attributes['created_at'],
                'updated_at' => $this->attributes['updated_at'],
                'deleted_at' => $this->attributes['deleted_at'],
            ]);
        }
        else {
            $jalali = to_jalali([
                'created_at' => $this->attributes['created_at'],
                'updated_at' => $this->attributes['updated_at'],
            ]);
        }
        $data = parent::toArray();
        $data['created_at'] = is_null($jalali['created_at']) ? null : $jalali['created_at']->formatDateTime();
        $data['updated_at'] = is_null($jalali['updated_at']) ? null : $jalali['updated_at']->formatDateTime();
        $data['deleted_at'] = !array_key_exists('deleted_at', $jalali) || is_null($this->attributes['deleted_at']) ? null : $jalali['deleted_at']->formatDateTime();
        $data['publish_date'] = !array_key_exists('publish_date', $jalali) || is_null($this->attributes['publish_date']) ? null : $jalali['publish_date']->formatDateTime();
        return $data;
    }
}
