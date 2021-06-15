<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\DeleteTicketRequest;
use App\Http\Requests\Ticket\DestroyTicketRequest;
use App\Http\Requests\Ticket\GetAllTicketsRequest;
use App\Http\Requests\Ticket\GetTicketRequest;
use App\Http\Requests\Ticket\RestoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Services\TicketService;

class TicketController extends Controller
{
    public function index(GetAllTicketsRequest $request)
    {
        return TicketService::getAllTickets($request);
    }

    public function view(GetTicketRequest $request)
    {
        return TicketService::getTicket($request);
    }

    public function create(CreateTicketRequest $request)
    {
        return TicketService::createTicket($request);
    }

    public function update(UpdateTicketRequest $request)
    {
        return TicketService::updateTicket($request);
    }

    public function delete(DeleteTicketRequest $request)
    {
        return TicketService::deleteTicket($request);
    }

    public function restore(RestoreTicketRequest $request)
    {
        return TicketService::restoreTicket($request);
    }

    public function destroy(DestroyTicketRequest $request)
    {
        return TicketService::destroyTicket($request);
    }

}
