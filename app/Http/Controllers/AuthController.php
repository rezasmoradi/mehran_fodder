<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\RegisterNewUserRequest;
use App\Services\UserService;

class AuthController extends Controller
{
    public function register(RegisterNewUserRequest $request)
    {
        return UserService::registerNewUser($request);
    }
}
