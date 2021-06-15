<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\CreatePaymentRequest;
use App\Http\Requests\Payment\DeletePaymentRequest;
use App\Http\Requests\Payment\GetAllPaymentRequest;
use App\Http\Requests\Payment\GetFinancialRequest;
use App\Http\Requests\Payment\GetPaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function index(GetAllPaymentRequest $request)
    {
        return PaymentService::getAllPayments($request);
    }

    public function financial(GetFinancialRequest $request)
    {
        return PaymentService::getFinancial($request);
    }

    public function view(GetPaymentRequest $request)
    {
        return PaymentService::getPayment($request);
    }

    public function create(CreatePaymentRequest $request)
    {
        return PaymentService::createPayment($request);
    }

    public function update(UpdatePaymentRequest $request)
    {
        return PaymentService::updatePayment($request);
    }

    public function delete(DeletePaymentRequest $request)
    {
        return PaymentService::deletePayment($request);
    }
}
