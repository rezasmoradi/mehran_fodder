<?php

namespace App\Listeners;


use App\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Events\AccessTokenCreated;

class ActiveUnregisteredUserAfterLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AccessTokenCreated  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        $user = User::withTrashed()->find($event->userId);
        if($user->trashed()){
            try {
                DB::beginTransaction();
                $user->restore();
                Log::info('active unregistered user', ['user_id' => $user->id]);
                DB::commit();
            } catch (Exception $exception) {
                Db::rollBack();
                Log::error($exception);
                throw $exception;
            }
        }
    }
}
