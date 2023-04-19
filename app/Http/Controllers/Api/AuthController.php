<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Models\ApiUser;
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
        $user = ApiUser::find('id', $request->id)->first();

        if (!$user || $user->is_verified) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $otp = $request->otp;

        if (!$otp || $user->otp != $otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $user->is_verified = true;
        $user->save();

        return response()->json(['message' => 'OTP verified successfully'], 200);
    }
}
