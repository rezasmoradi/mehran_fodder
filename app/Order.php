<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    //region CONSTANTS
    const TYPE_SALE = 'sale';
    const TYPE_PURCHASE = 'purchase';
    const ORDER_TYPES = [self::TYPE_SALE, self::TYPE_PURCHASE];
    //endregion CONSTANTS

    //region PROPERTIES
    protected $table = 'orders';

    protected $fillable = [
        'user_id', 'product_id', 'total_amount', 'payable_amount', 'discount',
        'pre_payment', 'type', 'description'
    ];
    //endregion PROPERTIES

    // region CUSTOM METHODS
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasOne(Product::class);
    }

    public function transportation()
    {
        return $this->hasMany(Transportation::class);
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }

    public function getUserOrderAttribute()
    {
        return User::query()->where('id', $this->getAttribute('user_id'))->first();
    }

    public function getProductOrderAttribute()
    {
        return Product::query()->where('id', $this->getAttribute('product_id'))->first();
    }

    public function getTransportationOrderAttribute()
    {
        return Transportation::query()->where('order_id', $this->getAttribute('id'))->get();
    }

    public function getPaymentOrderAttribute()
    {
        return Payment::withTrashed()->where('order_id', $this->getAttribute('id'))->get();
    }
    // endregion CUSTOM METHODS

    // region OVERRIDE METHODS
    public function toArray()
    {
        $jalali = to_jalali([
            'created_at' => $this->attributes['created_at'],
            'updated_at' => $this->attributes['updated_at'],
            'deleted_at' => $this->attributes['deleted_at'],
        ]);
        $data = parent::toArray();
        $data['user'] = $this->getUserOrderAttribute();
        $data['product'] = $this->getProductOrderAttribute();
        $data['created_at'] = $jalali['created_at']->formatDateTime();
        $data['updated_at'] = $jalali['updated_at']->formatDateTime();
        $data['deleted_at'] = is_null($jalali['deleted_at']) ? null : $jalali['deleted_at']->formatDateTime();
        switch (auth()->user()->type) {
            case  User::TYPE_ADMIN:
                $data['transportation'] = $this->getTransportationOrderAttribute();
                $data['payment'] = $this->getPaymentOrderAttribute();
                break;
            case User::TYPE_WAREHOUSE_KEEPER:
                $data['transportation'] = $this->getTransportationOrderAttribute();
                break;
            case User::TYPE_ACCOUNTANT:
                $data['payment'] = $this->getPaymentOrderAttribute();
                break;
            default:
                $data['payment'] = $order['payment'] = Payment::query()->where('order_id', $this->id)->get();
        }

        return $data;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($order) {
            $order->transportation()->delete();
            $order->payment()->delete();
        });

        static::restoring(function ($order) {
            $order->transportation()->restore();
            $order->payment()->restore();
        });
    }
    // endregion OVERRIDE METHODS
}
