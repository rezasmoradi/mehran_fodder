<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\DestroyProductRequest;
use App\Http\Requests\Product\GetAllProductRequest;
use App\Http\Requests\Product\GetProductRequest;
use App\Http\Requests\Product\RestoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function index(GetAllProductRequest $request)
    {
        return ProductService::getAllProducts($request);
    }

    public function view(GetProductRequest $request)
    {
        return ProductService::getProduct($request);
    }

    public function create(CreateProductRequest $request)
    {
        return ProductService::createProduct($request);
    }

    public function update(UpdateProductRequest $request)
    {
        return ProductService::updateProduct($request);
    }

    public function delete(DeleteProductRequest $request)
    {
        return ProductService::deleteProduct($request);
    }

    public function restore(RestoreProductRequest $request)
    {
        return ProductService::restoreProduct($request);
    }

    public function destroy(DestroyProductRequest $request)
    {
        return ProductService::destroyProduct($request);
    }
}
