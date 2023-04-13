<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function createAccount(RegisterRequest $request) {
        return $this->authService->createAccount($request->validated());
    }

    public function loginAccount(LoginRequest $request) {
        return $this->authService->loginAccount($request->validated());
    }

}