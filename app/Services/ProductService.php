<?php


namespace App\Services;


use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\DestroyProductRequest;
use App\Http\Requests\Product\GetAllProductRequest;
use App\Http\Requests\Product\GetProductRequest;
use App\Http\Requests\Product\RestoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService extends BaseService
{
    public static function getAllProducts(GetAllProductRequest $request)
    {
        $products = Product::withTrashed();
        $products = custom_response($products, $request);
        return response(['products' => $products->get()], 200);
    }

    public static function getProduct(GetProductRequest $request)
    {
        $product = Product::query()->where('id', $request->route('id'))->first();
        if (!is_null($product)) {
            return response(['product' => $product], 200);
        } else {
            throw new ModelNotFoundException('محصولی با این مشخصات یافت نشد.');
        }
    }

    public static function createProduct(CreateProductRequest $request)
    {
        try {
            DB::beginTransaction();
            Product::query()->create([
                'name' => $request->post('name'),
                'stock' => $request->post('stock'),
                'unit_price' => $request->post('unit_price'),
                'discount' => $request->post('discount'),
                'packing_weight' => $request->post('packing_weight')
            ]);
            DB::commit();
            return response(['message' => 'محصول جدید با موفقیت ثبت شد.'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'در ثبت محصول جدید مشکلی به وجود آمده است.'], 500);
        }
    }

    public static function updateProduct(UpdateProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::query()->where('id', $request->route('id'))->first();
            if ($product) {
                if ($request->has('stock')) $product->stock = $request->input('stock');
                if ($request->has('unit_price')) $product->unit_price = $request->input('unit_price');
                if ($request->has('discount')) $product->discount = $request->input('discount');
                if ($request->has('packing_weight')) $product->packing_weight = $request->input('packing_weight');
                $product->save();
            } else {
                throw new ModelNotFoundException('محصولی با این شناسه یافت نشد.');
            }
            DB::commit();
            return response(['product' => $product], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در بروز رسانی محصول مشکلی به وجود آمده است.'], 500);
        }
    }

    public static function deleteProduct(DeleteProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::query()->where('id', $request->route('id'))->first();
            if (!is_null($product)) {
                $product->delete();
            } else {
                throw new ModelNotFoundException('محصولی با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['message' => 'محصول با موفقیت حذف شد.'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در حذف محصول مشکلی به وجود آمده است.'], 500);
        }
    }

    public static function restoreProduct(RestoreProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::withTrashed()->where('id', $request->route('id'))->first();
            if ($product->trashed()) {
                $product->restore();
                DB::commit();
                return response(['product' => $product], 200);
            } else {
                throw new ModelNotFoundException('محصولی با این شناسه حذف نشده و یا وجود ندارد.');
            }
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) throw $e;
            Log::error($e);
            return response(['message' => 'در بازیابی محصول خطایی رخ داده است.'], 200);
        }
    }

    public static function destroyProduct(DestroyProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::withTrashed()->where('id', $request->route('id'))->first();
            if ($product->trashed()) {
                $product->forceDelete();
                DB::commit();
                return response(['message' => 'محصول با موفقیت از سیستم حذف شد'], 200);
            } else {
                throw new ModelNotFoundException('محصولی با این شناسه حذف نشده و یا وجود ندارد.');
            }
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) throw $e;
            Log::error($e);
            return response(['message' => 'در حذف محصول خطایی رخ داده است.'], 200);
        }
    }
}
