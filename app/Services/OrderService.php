<?php


namespace App\Services;

use App\Events\UpdateProductsEvent;
use App\Exceptions\ProductInventoryNotEnoughException;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\DeleteOrderRequest;
use App\Http\Requests\Order\GetAllOrdersRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\RestoreOrderRequest;
use App\Order;
use App\Payment;
use App\Ticket;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService extends BaseService
{
    public static function createOrder(CreateOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $payable = payable($request->post('product_id'), $request->post('total_amount'));
            $description = $request->post('descriptions') ? $request->post('descriptions') : null;
            $user = User::query()->where('id', auth()->id())->first();
            $employee_id = ($user->getAttribute('type') === User::TYPE_ORDER_RESPONSIBLE
                || $user->getAttribute('type') === User::TYPE_ADMIN)
                ? auth()->id()
                : null;
            $firstName = $user->first_name;
            $lastName = $user->last_name;
            if (!is_null($firstName) && !is_null($lastName)) {
                $order = Order::query()->create([
                    'user_id' => is_null($employee_id) ? $user->id : $request->post('user_id'),
                    'product_id' => $request->post('product_id'),
                    'employee_id' => $employee_id,
                    'total_amount' => $request->post('total_amount'),
                    'payable_amount' => $payable[1],
                    'discount' => $payable[0],
                    'pre_payment' => $request->post('pre_payment'),
                    'type' => $user->getAttribute('type') === User::TYPE_CUSTOMER ? 'sale' : $request->post('type'),
                    'descriptions' => $description,
                ]);
            } else {
                User::query()->where('id', $user->id)->update([
                    'first_name' => $request->post('first_name'), 'last_name' => $request->post('last_name')
                ]);
                $order = Order::query()->create([
                    'user_id' => $request->post('user_id'),
                    'product_id' => $request->post('product_id'),
                    'employee_id' => $employee_id,
                    'total_amount' => $request->post('total_amount'),
                    'payable_amount' => $payable[1],
                    'discount' => $payable[0],
                    'pre_payment' => $request->post('pre_payment'),
                    'type' => $user->getAttribute('type') === User::TYPE_CUSTOMER ? 'sale' : $request->post('type'),
                    'descriptions' => $description,
                ]);
            }
            $refId = generate_payment_number();
            $paymentAsCard = $request->has('payment_method')
                && $request->post('payment_method') === Payment::TYPE_CARD_TO_CARD;
            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'mobile' => $request->post('mobile'),
                'payed_amount' => $request->has('pre_payment') ? $request->post('pre_payment') : $request->post('payed_amount'),
                'method' => $request->has('payment_method') ? $request->post('payment_method') : Payment::TYPE_PAYMENT_GATEWAY,
                'ref_id' => $paymentAsCard ? $request->post('ref_id') : $refId,
            ]);

            event(new UpdateProductsEvent($order));
            DB::commit();
            return response([
                'message' => 'سفارش با موفقیت ثبت شد.',
                'order_id' => $order->id,
                'payable' => $order->payable_amount,
                'ref_id' => $payment->ref_id,
                'pre_payment' => $order->pre_payment,
            ],
                200);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ProductInventoryNotEnoughException) {
                throw $e;
            }
            Log::error($e);
            return response(['message' => 'در ثبت سفارش خطایی رخ داده است.'], 500);
        }
    }

    public static function deleteOrder(DeleteOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $order = Order::query()->where('id', $request->route('id'))->first();
            if ($order) {
                if ($user->type !== User::TYPE_CUSTOMER) {
                    $order->delete();
                } else {
                    Ticket::query()->create([
                        'user_id' => $user->getAuthIdentifier(),
                        'user_ip' => client_ip(),
                        'request_text' => 'درخواست حذف سفارش با مشخصات ' . $order,
                    ]);
                }
            } else {
                throw new ModelNotFoundException('سفارشی با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['message' => 'سفارش با موفقیت حذف شد.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) throw $e;
            Log::error($e);
            return response(['message' => 'در حذف سفارش مشکلی رخ داده است.'], 500);
        }
    }

    public static function getOrder(GetOrderRequest $request)
    {
        try {
            $order = Order::withTrashed()->where('id', $request->route('id'))->first();
            if (!is_null($order)) {
                return response(['order' => $order], 200);
            } else {
                throw new ModelNotFoundException('سفارشی با این مشخصات یافت نشد.');
            }
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در دریافت سفارش خطایی رخ داده است.'], 500);
        }
    }

    public static function getAllOrders(GetAllOrdersRequest $request)
    {
        try {
            $user = auth()->user();
            $orders = Order::withTrashed();
            if ($request->route('id')) {
                $orders = $orders->where('user_id', $request->route('id'));
                if (is_null($orders)) {
                    throw new ModelNotFoundException('سفارشی برای این کاربر یافت نشد.');
                }
            } else {
                $allowed = array_search($user->type, User::EMPLOYEE_TYPES);
                if ($allowed === false) {
                    $orders = $orders->where('user_id', auth()->id());
                }
            }

            $orders = custom_response($orders, $request);

            return response(['orders' => $orders->get()->toArray()], 200);
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در دریافت سفارشات خطایی رخ داده است.'], 200);
        }
    }

    public static function restoreOrder(RestoreOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $order = Order::withTrashed()->where('id', $request->route('id'))->first();
            if ($order) {
                $order->restore();
                DB::commit();
                return response(['order' => $order], 200);
            } else {
                throw new ModelNotFoundException('سفارشی با این شناسه یافت نشد.');
            }
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) throw $e;
            Log::error($e);
            return response(['message' => 'در بازیابی سفارش خطایی رخ داده است.'], 500);
        }
    }
}
