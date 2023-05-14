<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signUp(Request $request)
    {
        $result = $this->authService->register($request->all());

        return response()->json($result);
    }

    public function sendOTP(Request $request)
    {
        $result = $this->authService->sendOTP($request->all());

        return response()->json($result);
    }

    public function verifyOTP(Request $request)
    {
        $result = $this->authService->verifyOTP($request->all());

        return response()->json($result);
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->is_verified) {
                return response()->json(['error' => 'User not verified.'], 422);
            }
            $token = $user->createToken('name')->accessToken; 
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        
        return response()->json($user);
    }
}
