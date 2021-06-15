<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    //region CONSTANTS
    const TYPE_CHEQUE = 'cheque';
    const TYPE_CARD_TO_CARD = 'card_to_card';
    const TYPE_PAYMENT_GATEWAY = 'payment_gateway';
    const TYPE_OTHER = 'other_method';
    const PAYMENT_TYPES = [self::TYPE_CHEQUE, self::TYPE_CARD_TO_CARD, self::TYPE_PAYMENT_GATEWAY, self::TYPE_OTHER];
    //endregion

    protected $table = 'payments';

    protected $fillable = [
        'order_id', 'ref_id', 'mobile',
        'status', 'payed_amount', 'method', 'description'
    ];

    public function isGateway($payType)
    {
        return $payType === static::TYPE_PAYMENT_GATEWAY;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function user($userId)
    {
        return static::query()
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.user_id', $userId);
    }

    public static function pay_calc($orderId)
    {
        $order = Order::query()->where('id', $orderId)->first();
        $product = Product::query()->where('id', $order->product_id)->first();
        $pay = floor(($order->total_amount / $product->packing_weight) * ($product->unit_price - $product->discount));
        $discount = floor(($order->total_amount / $product->packing_weight) * $product->discount);
        return [$pay, $discount];
    }

    /*    public function payedByUser($orderId)
        {
            $pays = static::query()->select(['payed_amount'])->where('order_id', $orderId)->get();
            return array_reduce($pays->toArray(), function ($amount, $pay) {
                return $amount + $pay['payed_amount'];
            }, 0);
        }*/

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
        $data['created_at'] = is_null($jalali['created_at']) ? null : $jalali['created_at']->formatDateTime();
        $data['updated_at'] = is_null($jalali['updated_at']) ? null : $jalali['updated_at']->formatDateTime();
        $data['deleted_at'] = !array_key_exists('deleted_at', $jalali) || is_null($jalali['deleted_at']) ? null : $jalali['deleted_at']->formatDateTime();
        /*        $data['payed'] = $this->payedByUser($this->attributes['order_id']);
                $data['remaining'] = $this->attributes['real_amount'] - $data['payed'];*/
        return $data;
    }
}
