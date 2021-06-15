<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    //region CONSTANTS
    const TYPE_ADMIN = 'admin';
    const TYPE_WAREHOUSE_KEEPER = 'warehouse_keeper';
    const TYPE_ORDER_RESPONSIBLE = 'order_responsible';
    const TYPE_ACCOUNTANT = 'accountant';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_SELLER = 'seller';
    const EMPLOYEE_TYPES = [
        self::TYPE_ADMIN, self::TYPE_WAREHOUSE_KEEPER, self::TYPE_ACCOUNTANT, self::TYPE_ORDER_RESPONSIBLE
    ];
    const USER_TYPES = [
        self::TYPE_CUSTOMER, self::TYPE_SELLER, self::TYPE_ADMIN, self::TYPE_WAREHOUSE_KEEPER,
        self::TYPE_ACCOUNTANT, self::TYPE_ORDER_RESPONSIBLE
    ];
    //endregion CONSTANTS

    //region PROPERTIES

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'username', 'password', 'type', 'mobile', 'phone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    //endregion PROPERTIES

    public function findForPassport($value)
    {
        return static::withTrashed()
            ->where('username', $value)
            ->Orwhere('mobile', $value)
            ->first();
    }

    // region CUSTOM METHODS
    public function isAdmin()
    {
        return $this->attributes['type'] === self::TYPE_ADMIN;
    }

    public function isCustomer()
    {
        return $this->attributes['type'] === self::TYPE_CUSTOMER;
    }

    public function isUserType($type)
    {
        return $this->attributes['type'] === $type;
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }

    public function event()
    {
        return $this->hasMany(Event::class);
    }

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }

    public function view()
    {
        return $this->belongsToMany(UserEvent::class, 'users_events');
    }

    public function getUserAddressAttribute()
    {
        return Address::query()->where('user_id', $this->getAttribute('id'))->first();
    }

    public function getUserEvents($userId)
    {
        return UserEvent::query()
            ->join('events', 'events.id', '=', 'users_events.event_id')
            ->where('users_events.user_id', $userId)
            ->get();
    }

    public function getUserTickets($userId)
    {
        return Ticket::query()->where('user_id', $userId)->get();
    }

    public function getUserOrders($userId)
    {
        return Order::query()->where('user_id', $userId)->get();
    }

    // endregion CUSTOM METHODS

    //region override methods

    public function toArray()
    {
        if (array_key_exists('deleted_at', $this->attributes) && $this->attributes['deleted_at'] !== null) {
            $jalali = to_jalali([
                'created_at' => $this->attributes['created_at'],
                'updated_at' => $this->attributes['updated_at'],
                'deleted_at' => $this->attributes['deleted_at'],
            ]);
        } else {
            $jalali = to_jalali([
                'created_at' => $this->attributes['created_at'],
                'updated_at' => $this->attributes['updated_at'],
            ]);
        }
        $data = parent::toArray();
        $data['created_at'] = $jalali['created_at']->formatDateTime();
        $data['updated_at'] = $jalali['updated_at']->formatDateTime();
        if (array_key_exists('deleted_at', $jalali) && $this->attributes['deleted_at'] !== null) {
            $data['deleted_at'] = $jalali['deleted_at']->formatDateTime();
        }
        $data['address'] = $this->getUserAddressAttribute();
        if (auth()->check() && auth()->user()->type === self::TYPE_ADMIN) {
            $data['tickets'] = Ticket::all();
        } else {
//            $data['orders'] = $this->getUserOrders($this->attributes['id']);
            $data['events'] = $this->getUserEvents($this->attributes['id']);
            $data['tickets'] = $this->getUserTickets($this->attributes['id']);
        }

        return $data;
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($user) {
            if ($user->address()) {
                $user->address()->delete();
            }
            $user->ticket()->delete();
            if ($user->employee()) {
                $user->employee()->delete();
            }
        });

        static::restoring(function ($user) {
            if ($user->address()) {
                $user->address()->restore();
            }
            $user->ticket()->restore();
            $user->view()->restore();
            if ($user->employee()) {
                $user->employee()->restore();
            }
        });
    }
    //endregion override methods
}
