<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transportation\DeleteTransportationRequest;
use App\Http\Requests\Transportation\DestroyTransportationRequest;
use App\Http\Requests\Transportation\GetTransportationRequest;
use App\Http\Requests\Transportation\GetAllTransportationsRequest;
use App\Http\Requests\Transportation\CreateTransportationRequest;
use App\Http\Requests\Transportation\RestoreTransportationRequest;
use App\Http\Requests\Transportation\UpdateTransportationRequest;
use App\Services\TransportationService;

class TransportationController extends Controller
{
    public function index(GetAllTransportationsRequest $request)
    {
        return TransportationService::getAllTransports($request);
    }

    public function view(GetTransportationRequest $request)
    {
        return TransportationService::getTransportation($request);
    }

    public function create(CreateTransportationRequest $request)
    {
        return TransportationService::createTransportation($request);
    }

    public function update(UpdateTransportationRequest $request)
    {
        return TransportationService::updateTransportation($request);
    }

    public function delete(DeleteTransportationRequest $request)
    {
        return TransportationService::deleteTransportation($request);
    }

    public function restore(RestoreTransportationRequest $request)
    {
        return TransportationService::restoreTransportation($request);
    }

    public function destroy(DestroyTransportationRequest $request)
    {
        return TransportationService::destroyTransportation($request);
    }
}
