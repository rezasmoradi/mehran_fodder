<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\DeleteEventRequest;
use App\Http\Requests\Event\DestroyEventRequest;
use App\Http\Requests\Event\GetAllEventsRequest;
use App\Http\Requests\Event\GetEventRequest;
use App\Http\Requests\Event\RestoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Services\EventService;

class EventController extends Controller
{
    public function index(GetAllEventsRequest $request)
    {
        return EventService::getAllEvents($request);
    }

    public function view(GetEventRequest $request)
    {
        return EventService::getEvent($request);
    }

    public function create(CreateEventRequest $request)
    {
        return EventService::createEvent($request);
    }

    public function update(UpdateEventRequest $request)
    {
        return EventService::updateEvent($request);
    }

    public function delete(DeleteEventRequest $request)
    {
        return EventService::deleteEvent($request);
    }

    public function restore(RestoreEventRequest $request)
    {
        return EventService::restoreEvent($request);
    }

    public function destroy(DestroyEventRequest $request)
    {
        return EventService::destroyEvent($request);
    }
}
