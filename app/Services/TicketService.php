<?php


namespace App\Services;


use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\DeleteTicketRequest;
use App\Http\Requests\Ticket\DestroyTicketRequest;
use App\Http\Requests\Ticket\GetAllTicketsRequest;
use App\Http\Requests\Ticket\GetTicketRequest;
use App\Http\Requests\Ticket\RestoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Ticket;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService extends BaseService
{
    public static function getAllTickets(GetAllTicketsRequest $request)
    {
        try {
            $allTickets = [];
            $tickets = Ticket::withTrashed();
            if ($request->route('id')) {
                $tickets = $tickets->where('user_id', $request->route('id'));
                if (is_null($tickets)) {
                    throw new ModelNotFoundException('درخواستی از طرف این کاربر یافت نشد.');
                }
            }
            $tickets = custom_response($tickets, $request);
            $tickets->toBase();
            foreach ($tickets->get()->toArray() as $ticket) {
                preg_match('/^\d{3}.*$/', $ticket['user_ip'], $matches);
                if (empty($matches)) {
                    $ticket['user_ip'] = unserialize($ticket['user_ip']);
                    array_push($allTickets, $ticket);
                } else {
                    array_push($allTickets, $ticket);
                }
            }
            return response(['tickets' => $allTickets], 200);
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception->getMessage());
            return response(['message' => 'دریافت درخواست ها با خطا مواجه شد.'], 500);
        }
    }

    public static function createTicket(CreateTicketRequest $request)
    {
        try {
            DB::beginTransaction();
            $user_id = $request->user('api') ? $request->user('api')->id : null;

            Ticket::query()->create([
                'user_id' => $user_id,
                'user_ip' => $request->has('user') ? serialize(($request->post('user'))) : client_ip(),
                'request_text' => $request->post('request_text'),
                'status' => 0
            ]);
            DB::commit();
            return response(['message' => 'درخواست جدید با موفقیت ثبت شد.'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'در ثبت تیکت جدید خطایی رخ داده است.'], 500);
        }
    }

    public static function getTicket(GetTicketRequest $request)
    {
        $ticket = Ticket::query()->find($request->route('id'));
        switch (true) {
            case is_null($ticket):
                throw new ModelNotFoundException('درخواستی با این مشخصات یافت نشد.');
            case $ticket->status === 1 || ($ticket->status === 0 && $request->user('api')->isAdmin()):
                return response(['ticket' => $ticket], 200);
            default:
                unset($ticket->response_text);
                $ticket->response_text = null;
                return response(['ticket' => $ticket], 200);
        }
    }

    public static function updateTicket(UpdateTicketRequest $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::query()->find($request->route('id'));
            if (is_null($ticket)) {
                throw new ModelNotFoundException('درخواستی با این مشخصات یافت نشد.');
            } else {
                if ($request->has('response_text')) $ticket->response_text = $request->input('response_text');
                if ($request->has('status')) $ticket->status = $request->input('status');
                $ticket->save();
            }
            DB::commit();
            return response(['ticket' => $ticket], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در بروز رسانی درخواست خطایی رخ داده است.'], 500);
        }
    }

    public static function deleteTicket(DeleteTicketRequest $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::query()->find($request->route('id'));
            if (is_null($ticket)) {
                throw new ModelNotFoundException('درخواستی با این مشخصات یافت نشد.');
            } else {
                $ticket->delete();
            }
            DB::commit();
            return response(['message' => 'درخواست با موفقیت حذف شد.'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'در حذف درخواست خطایی رخ داده است.'], 500);
        }
    }

    public static function restoreTicket(RestoreTicketRequest $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::withTrashed()->where('id', $request->route('id'))->first();
            if ($ticket->trashed()) {
                $ticket->restore();
                DB::commit();
                return response(['message' => 'بازیابی درخواست با موفقیت انجام شد.'], 200);
            } else {
                throw new ModelNotFoundException('درخواستی با این شناسه یافت نشد.');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در بازیابی درخواست خطایی رخ داده است.'], 500);
        }
    }

    public static function destroyTicket(DestroyTicketRequest $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::withTrashed()->where('id', $request->route('id'))->first();
            if ($ticket->trashed()) {
                $ticket->forceDelete();
                DB::commit();
                return response(['message' => 'حذف درخواست از سیستم با موفقیت انجام شد.'], 200);
            } else {
                throw new ModelNotFoundException('گزارش درخواستی با این شناسه ثبت و یا حذف نشده است.');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در حذف درخواست خطایی رخ داده است.'], 500);
        }
    }
}
