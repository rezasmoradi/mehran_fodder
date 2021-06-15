<?php

namespace App;

use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = ['name', 'stock', 'unit_price', 'packing_weight'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function toArray()
    {
        $jalali = to_jalali([
            'created_at' => $this->attributes['created_at'],
            'updated_at' => $this->attributes['updated_at'],
            'deleted_at' => $this->attributes['deleted_at'],
        ]);
        $data = parent::toArray();
        $data['created_at'] = $jalali['created_at']->formatDateTime();
        $data['updated_at'] = $jalali['updated_at']->formatDateTime();
        $data['deleted_at'] = is_null($jalali['deleted_at']) ? null : $jalali['deleted_at']->formatDateTime();

        return $data;
    }
}
