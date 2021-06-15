<?php


namespace App\Services;

use App\Event;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\DeleteEventRequest;
use App\Http\Requests\Event\DestroyEventRequest;
use App\Http\Requests\Event\GetAllEventsRequest;
use App\Http\Requests\Event\GetEventRequest;
use App\Http\Requests\Event\RestoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\User;
use App\UserEvent;
use Exception;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventService extends BaseService
{
    public static function getAllEvents(GetAllEventsRequest $request)
    {
        try {
            $events = Event::withTrashed();
            $unpublished = [];
            $published = [];
            if ($request->route('id')) {
                $events = $events->where('id', $request->route('id'));
                if (is_null($events)) {
                    throw new ModelNotFoundException('رویدادی با این مشخصات یافت نشد.');
                }
            }
            $events = custom_response($events, $request);
            $events->toBase();
            foreach ($events->get()->toArray() as $publish) {
                if (!is_null($publish['publish_date']) && $publish['publish_date'] > Verta::now()->timezone('Asia/Tehran')->formatDatetime()) {
                    array_push($unpublished, $publish);
                } elseif (is_null($publish['publish_date'])) {
                    array_push($published, $publish);
                } else {
                    array_push($published, $publish);
                }
            }
            if ($request->user('api')->isAdmin()) {
                return response(['published' => $published, 'unpublished' => $unpublished], 200);
            } else {
                return response(['published' => $published], 200);
            }
        } catch (Exception $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'دریافت رویدادها با خطا مواجه شد.'], 500);

        }
    }

    public static function getEvent(GetEventRequest $request)
    {
        try {
            DB::beginTransaction();
            $event = Event::query()->where('id', $request->route('id'))->first();
            if (is_null($event)) throw new ModelNotFoundException('رویدادی با این شناسه یافت نشد.');
            else {
                $receivers = UserEvent::query()->select('user_id')->where('event_id', $event->id)->get();
                $users = User::query()->whereIn('id', $receivers)->get();
                UserEvent::query()
                    ->where(['event_id' => $event->id, 'user_id' => $request->user('api')->id])
                    ->update(['read_status' => 1]);
                DB::commit();
                return response(['event' => $event, 'users' => $users], 200);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در دریافت رویداد خطایی رخ داده است.'], 500);
        }
    }

    public static function createEvent(CreateEventRequest $request)
    {
        try {
            DB::beginTransaction();
            $event = Event::query()->create([
                'event_title' => $request->post('event_title'),
                'event_content' => $request->post('event_content'),
                'publish_date' => $request->post('publish_date'),
            ]);
            foreach ($request->all()['users'] as $user) {
                UserEvent::query()->create([
                    'event_id' => $event->id,
                    'user_id' => $user,
                ]);
            }
            DB::commit();
            return response(['message' => 'رویداد با موفقیت ثبت شد.'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'در ثبت رویداد خطایی رخ داده است. '], 500);
        }
    }

    public static function updateEvent(UpdateEventRequest $request)
    {
        try {
            $event = Event::query()->where('id', $request->route('id'))->first();
            if (is_null($event)) {
                throw new ModelNotFoundException('رویدادی با این مشخصات یافت نشد.');
            } else {
                if ($request->has('users')) {
                    $exists = UserEvent::query()
                        ->select('user_id')
                        ->where('event_id', $event->id)
                        ->pluck('user_id')->toArray();
                    foreach ($request->all()['users'] as $user) {
                        if (array_search($user, $exists) === false) {
                            UserEvent::query()->create([
                                'event_id' => $event->id,
                                'user_id' => $user
                            ]);
                        }
                    }
                }
                if ($request->has('event_title')) $event->event_title = $request->input('event_title');
                if ($request->has('event_content')) $event->event_content = $request->input('event_content');
                if ($request->has('publish_date')) $event->publish_date = $request->input('publish_date');
                $event->save();
                DB::commit();
                return response(['message' => 'رویداد با موفقیت بروز رسانی شد.'], 200);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در بروز رسانی رویداد خطایی رخ داده است.'], 500);
        }
    }

    public static function deleteEvent(DeleteEventRequest $request)
    {
        try {
            DB::beginTransaction();
            $event = Event::query()->find($request->route('id'));
            if (is_null($event)) {
                throw new ModelNotFoundException('رویدادی با این مشخصات یافت نشد.');
            } else {
                $event->delete();
                DB::commit();
                return response(['message' => 'رویداد با موفقیت حذف شد.'], 200);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در حذف رویداد خطایی رخ داده است.'], 500);
        }
    }

    public static function restoreEvent(RestoreEventRequest $request)
    {
        try {
            DB::beginTransaction();
            $event = Event::withTrashed()->where('id', $request->route('id'))->first();
            if ($event->trashed()) {
                $event->restore();
                DB::commit();
                return response(['message' => 'بازیابی رویداد با موفقیت انجام شد.'], 200);
            } else {
                throw new ModelNotFoundException('رویدادی با این شناسه یافت نشد.');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در بازیابی رویداد خطایی رخ داده است.'], 500);
        }
    }

    public static function destroyEvent(DestroyEventRequest $request)
    {
        try {
            DB::beginTransaction();
            $event = Event::withTrashed()->where('id', $request->route('id'))->first();
            if ($event->trashed()) {
                $event->forceDelete();
                DB::commit();
                return response(['message' => 'حذف رویداد از سیستم با موفقیت انجام شد.'], 200);
            } else {
                throw new ModelNotFoundException('رویدادی با این شناسه ثبت و یا حذف نشده است.');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) throw $exception;
            Log::error($exception);
            return response(['message' => 'در حذف رویداد خطایی رخ داده است.'], 500);
        }
    }
}
