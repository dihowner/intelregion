<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\UpgradePlanRequest;
use App\Http\Requests\UpdatePasswordRequest;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function GenerateUserVirtualAccount() {
        return $this->userService->GenerateUserVirtualAccount();
    }

    public function GetUser($userId) {
        return $this->userService->getUserById($userId);
    }

    public function ModifyUserPassword(UpdatePasswordRequest $request) {
        return $this->userService->ModifyUserPassword($request->validated());
    }

    public function UpgradePlan(UpgradePlanRequest $request) {
        return $this->userService->UpgradePlan($request->validated());
    }

}