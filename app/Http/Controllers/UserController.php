<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\GetAllUsersRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\GetMeInfoRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\RegisterNewUserRequest;
use App\Http\Requests\User\RestoreUserRequest;
use App\Http\Requests\User\UnregisterUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UserLogoutRequest;
use App\Services\UserService;

class UserController extends Controller
{
    public function index(GetAllUsersRequest $request)
    {
        return UserService::getAllUsers($request);
    }

    public function view(GetUserRequest $request)
    {
        return UserService::getUser($request);
    }

    public function me()
    {
        return UserService::getMe();
    }

    public function create(CreateUserRequest $request)
    {
        return UserService::createUser($request);
    }

    public function register(RegisterNewUserRequest $request){
        return UserService::registerNewUser($request);
    }

    public function update(UpdateUserRequest $request)
    {
        return UserService::updateUser($request);
    }

    public function delete(DeleteUserRequest $request)
    {
        return UserService::deleteUser($request);
    }

    public function unregister(UnregisterUserRequest $request)
    {
        return UserService::unregister($request);
    }

    public function logout(UserLogoutRequest $request)
    {
        return UserService::logoutUser($request);
    }
}
