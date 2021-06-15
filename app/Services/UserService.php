<?php

namespace App\Services;

use App\Address;
use App\Employee;
use App\Exceptions\UserAlreadyRegisteredException;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\GetAllUsersRequest;
use App\Http\Requests\User\GetMeInfoRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\RegisterNewUserRequest;
use App\Http\Requests\User\UnregisterUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UserLogoutRequest;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    public static function getAllUsers(GetAllUsersRequest $request)
    {
        $users = User::withTrashed();
        $userType = $request->user()->type;
        if ($userType === User::EMPLOYEE_TYPES && $userType !== User::TYPE_ADMIN) {
            $users = $users->whereNotIn('type', User::EMPLOYEE_TYPES);
        }
        if ($request->route('id')) {
            $users = $users->where('id', $request->route('id'));
            if (is_null($users)) throw new ModelNotFoundException('کاربری با این شناسه یافت نشد.');
        }

        $users = custom_response($users, $request);
        return response(['users' => $users->get()], 200);
    }

    public static function getUser(GetUserRequest $request)
    {
        $user = User::query()->where('id', $request->route('id'))->first();
        return response(['user' => $user], 200);
    }

    public static function getMe()
    {
        $user = auth()->user();
        return response(['user' => $user], 200);
    }

    public static function registerNewUser(RegisterNewUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $mobile = $request->input('mobile');
            if ($user = User::query()->where('mobile', $mobile)->first() ||
                $address = Address::query()->where('postal_code', $request->post('postal_code'))->first()) {
                throw new UserAlreadyRegisteredException('شما قبلاً ثبت نام کرده اید.');
            }
            $user = User::query()->create([
                'mobile' => $mobile,
                'password' => bcrypt($request->input('password')),
                'username' => $request->input('username'),
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'type' => User::TYPE_CUSTOMER
            ]);
            Address::query()->create([
                'user_id' => $user->id,
                'province' => $request->post('province'),
                'city' => $request->post('city'),
                'village' => $request->post('village'),
                'street' => $request->post('street'),
                'postal_code' => $request->post('postal_code')
            ]);
            DB::commit();
            return response(['user' => $user], 201);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof UserAlreadyRegisteredException) {
                throw $e;
            }
            Log::error($e);
            return response(['message' => 'ثبت نام با مشکل روبرو شد؛ مجدداً تلاش کنید.'], 500);
        }
    }

    public static function createUser(CreateUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $mobile = $request->post('mobile');
            $type = $request->post('type');
            if ($user = User::query()->where('mobile', $mobile)->first() ||
                $address = Address::query()->where('postal_code', $request->post('postal_code'))->first()) {
                throw new UserAlreadyRegisteredException('کاربر قبلاً ثبت نام شده است.');
            }
            $user = User::query()->create([
                'first_name' => $request->post('first_name'),
                'last_name' => $request->post('last_name'),
                'username' => $request->post('username'),
                'password' => bcrypt($request->post('password')),
                'type' => $type,
                'mobile' => $mobile,
                'phone' => $request->post('phone'),
            ]);
            Address::query()->create([
                'user_id' => $user->id,
                'province' => $request->post('province'),
                'city' => $request->post('city'),
                'village' => $request->post('village'),
                'street' => $request->post('street'),
                'postal_code' => $request->post('postal_code')
            ]);
            if ($type !== User::TYPE_CUSTOMER && $type !== User::TYPE_SELLER) {
                Employee::query()->create([
                    'user_id' => $user->getAttribute('id'),
                    'employee_code' => generate_employee_code(),
                    'employed_at' => !is_null($request->post('employed_at')) ? $request->post('employed_at') : now()
                ]);
            }
            DB::commit();
            return response(['user' => $user], 201);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof UserAlreadyRegisteredException) {
                throw $e;
            }
            Log::error($e);
            return response(['message' => 'ثبت نام با مشکل روبرو شد؛ مجدداً تلاش کنید.'], 500);
        }
    }

    public static function updateUser(UpdateUserRequest $request)
    {
        if ($request->route('id')) {
            $user = User::query()->where('id', $request->route('id'))->first();
        } else {
            $user = User::query()->where('id', auth()->id())->first();
        }
        try {
            DB::beginTransaction();
            if ($user) {
                if (!empty($request->all())) {
                    $addressRequests = ['province', 'city', 'village', 'street', 'postal_code'];
                    $address = Address::query()->where('user_id', $user->id)->first();
                    if ($request->has('first_name')) $user->first_name = $request->input('first_name');
                    if ($request->has('last_name')) $user->last_name = $request->input('last_name');
                    if ($request->has('username')) $user->username = $request->input('username');
                    if ($request->has('password')) $user->password = bcrypt($request->input('password'));
                    if ($request->has('phone')) $user->phone = $request->input('phone');
                    $user->save();
                    foreach ($request->keys() as $key) {
                        if (array_search($key, $addressRequests) !== false) {
                            if ($request->has('province')) $address->province = $request->input('province');
                            if ($request->has('city')) $address->city = $request->input('city');
                            if ($request->has('village')) $address->village = $request->input('village');
                            if ($request->has('street')) $address->street = $request->input('street');
                            if ($request->has('postal_code')) $address->postal_code = $request->input('postal_code');
                            $address->save();
                        }
                    }
                }
            } else {
                throw new ModelNotFoundException('کاربری با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['user' => $user], 202);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof UserAlreadyRegisteredException) {
                throw $e;
            }
            if ($e instanceof ModelNotFoundException) {
                throw $e;
            }
            Log::error($e);
            return response(['message' => 'هنگام بروز رسانی کاربر، خطایی به وجود آمد؛ مجدداً تلاش کنید.'], 500);
        }
    }

    public static function unregister(UnregisterUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::query()->where('id', $request->user()->id)->first();
            $user->delete();
            DB::table('oauth_access_tokens')
                ->where('user_id', $request->user()->id)
                ->delete();

            DB::commit();
            return response(['message' => 'کاربر با موفقیت غیر فعال شد، برای فعالسازی کافیست یک بار در سیستم لاگین کنید'], Response::HTTP_OK);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            Log::error($exception);
            return response(['message' => 'عملیات مورد نظر مقدور نمیباشد، دوباره سعی کنید'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public static function deleteUser(DeleteUserRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->route('id')) {
                $user = User::query()->where('id', $request->route('id'))->first();
            } else {
                $user = User::query()->where('id', $request->user()->id)->first();
            }
            if ($user) {
                $user->forceDelete();
            } else {
                throw new ModelNotFoundException('کاربری با این مشخصات یافت نشد.');
            }
            DB::commit();
            return response(['message' => 'کاربر با موفقیت حذف شد.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ModelNotFoundException) {
                throw $e;
            }
            Log::error($e);
            return response(['message' => 'حذف کاربر با خطا مواجه شد.'], 500);
        }
    }

    public static function logoutUser(UserLogoutRequest $request)
    {
        try {
            $request->user()->token()->revoke();
            return response(['message' => 'خروج با موفقیت انجام شد'], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e);
        }

        return response(['message' => 'عملیات خروج ناموفق بود'], Response::HTTP_BAD_REQUEST);
    }
}
