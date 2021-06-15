<?php


namespace App\Services;


use App\Http\Requests\Transportation\DeleteTransportationRequest;
use App\Http\Requests\Transportation\DestroyTransportationRequest;
use App\Http\Requests\Transportation\GetTransportationRequest;
use App\Http\Requests\Transportation\GetAllTransportationsRequest;
use App\Http\Requests\Transportation\CreateTransportationRequest;
use App\Http\Requests\Transportation\RestoreTransportationRequest;
use App\Http\Requests\Transportation\UpdateTransportationRequest;
use App\Transportation;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransportationService extends BaseService
{

    public static function getAllTransports(GetAllTransportationsRequest $request)
    {
        try {
            $transportations = Transportation::withTrashed();

            if ($request->route('id')) {
                $transportations = $transportations
                    ->leftJoin('orders', 'orders.id', '=', 'transportations.order_id')
                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
                    ->where('users.id', $request->route('id'));
                if (is_null($transportations)) {
                    throw new ModelNotFoundException('حمل و نقلی برای این سفارش یافت نشد.');
                }
            } else {
                if (auth()->user()->type === User::TYPE_CUSTOMER) {
                    $transportations = $transportations
                        ->join('orders', 'orders.id', '=', 'transportations.order_id')
                        ->where('orders.user_id', auth()->id());
                }
            }
            $transportations = custom_response($transportations, $request);
            return response(['transportations' => $transportations->get()], 200);
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در دریافت سفارشات خطایی رخ داده است.'], 500);
        }
    }

    public static function getTransportation(GetTransportationRequest $request)
    {
        try {
            $transportation = Transportation::query()->where('id', $request->route('id'))->first();
            if (!$transportation) {
                throw new ModelNotFoundException('گزارش حمل و نقلی با این مشخصات یافت نشد.');
            } else {
                return response(['transportations' => $transportation], 200);
            }
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'دریافت گزارش حمل و نقل با خطا مواجه شد.'], 500);
        }
    }

    public static function createTransportation(CreateTransportationRequest $request)
    {
        try {
            DB::beginTransaction();
            Transportation::query()->create([
                'order_id' => $request->post('order_id'),
                'license_plate' => $request->post('license_plate'),
                'vehicle_name' => $request->post('vehicle_name'),
                'delivery_amount' => $request->post('delivery_amount'),
                'delivery_at' => $request->post('delivery_at'),
                'descriptions' => $request->post('descriptions')
            ]);
            DB::commit();
            return response(['message' => 'گزارش حمل و نقل با موفقیت ایجاد شد.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response(['message' => 'در ثبت گزارش حمل و نقل خطایی رخ داده است.'], 500);
        }
    }

    public static function updateTransportation(UpdateTransportationRequest $request)
    {
        try {
            DB::beginTransaction();
            $transportation = Transportation::query()->find($request->route('id'));
            if (!is_null($transportation)) {
                if ($request->has('order_id')) $transportation->order_id = $request->input('order_id');
                if ($request->has('license_plate')) $transportation->license_plate = $request->input('license_plate');
                if ($request->has('vehicle_name')) $transportation->vehicle_name = $request->input('vehicle_name');
                if ($request->has('delivery_amount')) $transportation->delivery_amount = $request->input('delivery_amount');
                if ($request->has('delivery_at')) $transportation->delivery_at = $request->input('delivery_at');
                if ($request->has('descriptions')) $transportation->descriptions = $request->input('descriptions');
                $transportation->save();
            } else {
                throw new ModelNotFoundException('گزارش حمل و نقلی با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['transportation' => $transportation], 200);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) {
                throw $e;
            }
            Log::error($e);
            return response(['message' => 'در بروز رسانی گزارش حمل و نقل خطایی رخ داده است.'], 500);
        }
    }

    public static function deleteTransportation(DeleteTransportationRequest $request)
    {
        try {
            DB::beginTransaction();
            $transportation = Transportation::query()->where('id', $request->route('id'))->first();
            if ($transportation) {
                $transportation->delete();
            } else {
                throw new ModelNotFoundException('گزارش حمل و نقلی با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['message' => 'حذف گزارش حمل و نقل با موفقیت انجام شد.'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در حذف گزارش حمل و نقل خطایی رخ داده است.'], 500);
        }
    }

    public static function restoreTransportation(RestoreTransportationRequest $request)
    {
        try {
            DB::beginTransaction();
            $transportation = Transportation::withTrashed()->where('id', $request->route('id'))->first();
            if ($transportation->trashed()) {
                $transportation->restore();
                DB::commit();
                return response(['message' => 'بازیابی گزارش حمل و نقل با موفقیت انجام شد.'], 200);
            } else {
                throw new ModelNotFoundException('گزارش حمل و نقلی با این شناسه یافت نشد.');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در بازیابی گزارش حمل و نقل خطایی رخ داده است.'], 500);
        }
    }

    public static function destroyTransportation(DestroyTransportationRequest $request)
    {
        try {
            DB::beginTransaction();
            $transportation = Transportation::withTrashed()->where('id', $request->route('id'))->first();
            if ($transportation->trashed()) {
                $transportation->forceDelete();
                DB::commit();
                return response(['message' => 'حذف گزارش حمل و نقل از سیستم با موفقیت انجام شد.'], 200);
            } else {
                throw new ModelNotFoundException('گزارش حمل و نقلی با این شناسه ثبت و یا حذف نشده است.');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در حذف گزارش حمل و نقل خطایی رخ داده است.'], 500);
        }
    }
}
