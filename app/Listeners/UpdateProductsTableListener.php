<?php

namespace App\Listeners;

use App\Events\UpdateProductsEvent;
use App\Exceptions\ProductInventoryNotEnoughException;
use App\Order;
use App\Product;

class UpdateProductsTableListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UpdateProductsEvent $event
     * @return void
     * @throws ProductInventoryNotEnoughException
     */
    public function handle(UpdateProductsEvent $event)
    {
        $order = $event->getOrder();
        $productId = $order->getAttribute('product_id');
        $orderType = $order->getAttribute('type');
        $totalAmount = $order->getAttribute('total_amount');
        $product = Product::query()->where('id', $productId)->first();
        if ($orderType === Order::TYPE_SALE) {
            $productStock = $product->getAttribute('stock') - $totalAmount;
            if ($productStock < 0) {
                throw new ProductInventoryNotEnoughException('موجودی محصول در انبار کافی نیست.');
            } else {
                Product::query()->where('id', $productId)->update(['stock' => $productStock]);
            }
        }/* else {
            $productStock = $product->getAttribute('stock') + $totalAmount;
            Product::query()->where('id', $productId)->update(['stock' => $productStock]);
        }*/
    }
}
