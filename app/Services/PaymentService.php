<?php


namespace App\Services;


use App\Http\Requests\Payment\CreatePaymentRequest;
use App\Http\Requests\Payment\DeletePaymentRequest;
use App\Http\Requests\Payment\GetAllPaymentRequest;
use App\Http\Requests\Payment\GetFinancialRequest;
use App\Http\Requests\Payment\GetPaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Order;
use App\Payment;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService extends BaseService
{
    public static function getAllPayments(GetAllPaymentRequest $request)
    {
        try {
            $payments = Payment::withTrashed();
            if ($request->route('id')) {
                $payments = Payment::user($request->route('id'));
                if (is_null($payments)) {
                    throw new ModelNotFoundException('پرداختی برای این سفارش یافت نشد.');
                }
            } elseif (auth()->user()->type === User::TYPE_CUSTOMER) {
                $payments = Payment::user(auth()->id());
            }

            $payments = custom_response($payments, $request);
            return response(['payments' => $payments->get()], 200);

        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'دریافت گزارش پرداخت با خطا مواجه شد.'], 500);
        }
    }

    public static function getPayment(GetPaymentRequest $request)
    {
        try {
            $payment = Payment::withTrashed()->where('ref_id', $request->route('id'))->first();
            return response(['payment' => $payment], 200);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'دریافت گزارش پرداخت با خطا مواجه شد.'], 500);
        }
    }

    public static function updatePayment(UpdatePaymentRequest $request)
    {
        try {
            DB::beginTransaction();
            $payment = Payment::query()->where('ref_id', $request->route('ref_id'))->first();
            if (!is_null($payment)) {
                if ($request->has('payed_amount')) $payment->payed_amount = $request->input('payed_amount');
                if ($request->has('method') && $payment->method !== Payment::TYPE_PAYMENT_GATEWAY)
                    $payment->method = $request->input('payment_method');
                if ($request->has('ref_id') && $payment->method === Payment::TYPE_CARD_TO_CARD)
                    $payment->ref_id = $request->input('ref_id');
                if ($request->has('status')) $payment->status = $request->input('status');
                if ($request->has('descriptions')) $payment->description = $request->input('description');
                $payment->save();

                if ($request->input('status') === 0
                    && $payment->method === Payment::TYPE_PAYMENT_GATEWAY
                    && $payment->created_at < now()->subMinutes(16)) {
                    Order::query()->where('order_id', $request->input('order_id'))->update(['pre_payment' => 0]);
                }
            } else {
                throw new ModelNotFoundException('گزارش پرداختی با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['payment' => $payment], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در بروز رسانی گزارش پرداخت خطایی رخ داده است.'], 500);
        }
    }

    public static function createPayment(CreatePaymentRequest $request)
    {
        try {
            DB::beginTransaction();
            $orderId = $request->post('order_id');
            $paymentAsCard = $request->has('method')
                && $request->post('method') === Payment::TYPE_CARD_TO_CARD;
            $currentPayment = Payment::query()->create([
                'order_id' => $orderId,
                'ref_id' => $paymentAsCard ? $request->post('ref_id') : generate_payment_number(),
                'mobile' => $request->post('mobile'),
                'payed_amount' => $request->post('payed_amount'),
                'method' => $request->post('method'),
                'status' => $request->post('status'),
                'descriptions' => $request->post('descriptions'),
            ]);
            $firstPayment = Payment::query()->where(['order_id' => $orderId, 'status' => 1])->first();
            if ($firstPayment !== $currentPayment) {
                Order::query()->where('id', $orderId)->update(['pre_payment' => $currentPayment->payed_amount]);
            }
            DB::commit();
            return response(['message' => 'گزارش پرداخت با موفقیت ایجاد شد.', 'data' => $currentPayment], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'در ثبت گزارش پرداخت جدید خطایی رخ داده است.'], 500);
        }
    }

    public static function deletePayment(DeletePaymentRequest $request)
    {
        try {
            DB::beginTransaction();
            $payment = Payment::query()->where('id', $request->route('id'))->first();
            if (!is_null($payment)) {
                $payment->delete();
            } else {
                throw new ModelNotFoundException('گزارش پرداختی با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['message' => 'حذف گزارش پرداخت با موفقیت انجام شد.'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در حذف گزارش پرداخت خطایی رخ داده است.'], 500);

        }
    }

    public static function getFinancial(GetFinancialRequest $request)
    {
        $unionOnSale = DB::table('payments')
            ->selectRaw('COUNT(*) as pay_count, YEAR(o.created_at), SUM(payments.payed_amount) as pays, o.type, SUM(o.total_amount) as sale')
            ->leftJoin('orders AS o', 'o.id', '=', 'payments.order_id')
            ->whereYear('o.created_at', now()->toArray()['year'])
            ->groupBy('o.type');
        $query = DB::table('payments')
            ->selectRaw('COUNT(*) as pay_count, YEAR(o.created_at), SUM(payments.payed_amount) as pays, o.type, SUM(o.total_amount) as purchase')
            ->leftJoin('orders AS o', 'o.id', '=', 'payments.order_id')
            ->whereYear('o.created_at', now()->toArray()['year'])
            ->groupBy('o.type')
            ->union($unionOnSale);

        return $query->get();
    }
}
