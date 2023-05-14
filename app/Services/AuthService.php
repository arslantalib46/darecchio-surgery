<?php

namespace App\Services;

use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data)
    {
        // Validate the data
        $validator = Validator::make($data, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|unique:users,mobile',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => $validator->errors()->first()];
        }

        // Generate OTP and save it in the database
        $otp = rand(100000, 999999);
        $user = new User([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password']),
            'otp' => $otp,
            'otp_expiry' => now()->addMinutes(10),
        ]);
        $user->save();

        $data = [
            'name' => $data['first_name'].' '.$data['last_name'],
            'message' => $otp
        ];

        $subject = 'Your OTP for registration';
        // Send OTP email to user
        Mail::to($user->email)->send(new ContactFormMail($subject, $data));

        return ['status' => 'success', 'message' => 'OTP sent to your email'];
    }

    public function sendOTP(array $data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => $validator->errors()->first()];
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->is_verified) {
            return response()->json(['error' => 'User already verified.'], 422);
        }

        // Send OTP to user
        if ($user->otp_expiry < now()) {
            $user->otp = rand(100000, 999999);
            $user->otp_expiry = now()->addMinutes(10);
            $user->save();

            $data = [
                'name' => $user['first_name'].' '.$user['last_name'],
                'message' => $user->otp
            ];
    
            $subject = 'Your OTP for verfication';
            // Send OTP email to user
            Mail::to($user->email)->send(new ContactFormMail($subject, $data));
        }
        

        return ['status' => 'success', 'message' => $user->otp];
    }

    public function verifyOTP(array $data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|string|email|max:255',
            'otp' => 'required|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return ['status' => 'error', 'message' => 'User not found'];
        }

        if ($user->is_verified) {
            return response()->json(['error' => 'User already verified.'], 422);
        }

        $otpExpiry = Carbon::parse($user->otp_expiry);

        if (Carbon::now()->diffInMinutes($otpExpiry) > 10) {
            return response()->json(['error' => 'OTP has expired'], 401);
        }

        if ($user->otp != $data['otp']) {
            return ['status' => 'error', 'message' => 'Invalid OTP'];
        }

        // Mark the user as verified and delete the OTP
        $user->is_verified = true;
        $user->save();

        return ['status' => 'success', 'message' => 'User verified'];
    }
}