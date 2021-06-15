<?php

namespace App\Http\Controllers;

use App\Exceptions\ProductInventoryNotEnoughException;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\DeleteOrderRequest;
use App\Http\Requests\Order\GetAllOrdersRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\RestoreOrderRequest;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function create(CreateOrderRequest $request)
    {
        try {
            return OrderService::createOrder($request);
        } catch (ProductInventoryNotEnoughException $e) {
            throw $e;
        }
    }

    public function delete(DeleteOrderRequest $request)
    {
        return OrderService::deleteOrder($request);
    }

    public function view(GetOrderRequest $request)
    {
        return OrderService::getOrder($request);
    }

    public function index(GetAllOrdersRequest $request)
    {
        return OrderService::getAllOrders($request);
    }

    public function restore(RestoreOrderRequest $request)
    {
        return OrderService::restoreOrder($request);
    }
}
