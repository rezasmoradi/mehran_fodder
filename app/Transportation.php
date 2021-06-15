<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transportation extends Model
{
    use SoftDeletes;

    protected $table = 'transportations';

    protected $fillable = [
        'order_id', 'license_plate', 'vehicle_name', 'delivery_amount',
        'delivery_at', 'descriptions'
    ];

    protected $hidden = [];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user($userId)
    {
        return static::query()
            ->join('orders', 'orders.id', '=', 'transportations.order_id')
            ->where('orders.user_id', $userId);
    }

    public function toArray()
    {
        $jalali = to_jalali([
            'delivery_at' => $this->attributes['delivery_at'],
            'created_at' => $this->attributes['created_at'],
            'updated_at' => $this->attributes['updated_at'],
            'deleted_at' => $this->attributes['deleted_at'],
        ]);

        $data = parent::toArray();

        $data['delivery_at'] = $jalali['delivery_at']->format('Y-m-d H:i');
        $data['created_at'] = $jalali['created_at']->formatDateTime();
        $data['updated_at'] = $jalali['updated_at']->formatDateTime();
        $data['deleted_at'] = is_null($jalali['deleted_at']) ? null : $jalali['deleted_at']->formatDateTime();
        return $data;
    }
}
