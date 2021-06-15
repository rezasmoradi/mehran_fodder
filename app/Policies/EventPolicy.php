<?php

namespace App\Policies;

use App\Event;
use App\User;
use App\UserEvent;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    public function viewAll(User $user, Event $event)
    {
        return $user->isAdmin() || $user->id === $event->user_id;
    }

    public function view(User $user, Event $event)
    {
        return $user->isAdmin() || UserEvent::query()->where(['event_id' => $event->id, 'user_id' => $user->id]);
    }

    public function delete(User $user, Event $event)
    {
        return $user->isAdmin() || $user->id === $event->user()->id;
    }
}
