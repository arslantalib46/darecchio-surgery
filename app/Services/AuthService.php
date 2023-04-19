<?php

namespace App\Services;

use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\ApiUser;

class AuthService
{
    public function register(array $data)
    {
        // Validate the data
        $validator = Validator::make($data, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:api_users,email',
            'mobile' => 'required|unique:api_users,mobile',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => $validator->errors()->first()];
        }

        // Generate OTP and save it in the database
        $otp = rand(100000, 999999);
        $user = new ApiUser([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => bcrypt($data['password']),
            'otp' => $otp,
        ]);
        // $user->save();

        $data = [
            'name' => $data['first_name'].' '.$data['last_name'],
            'message' => $otp
        ];

        $subject = 'Your OTP for registration';
        // Send OTP email to user
        Mail::to($user->email)->send(new ContactFormMail($subject, $data));

        // Mail::send('emails.contact', ['otp' => $otp], function ($messages) use ($user) {
        //     $messages->to($user->email)->subject('Your OTP for registration');
        // });

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

        $user = ApiUser::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Send OTP to user
        $otp = rand(100000, 999999);
        
        $data = [
            'name' => $user['first_name'].' '.$user['last_name'],
            'message' => $otp
        ];

        $subject = 'Your OTP for verfication';
        // Send OTP email to user
        Mail::to($user->email)->send(new ContactFormMail($subject, $data));
        // Mail::send('emails.otp', $data, function($message) use ($user) {
        //     $message->to($user->email)->subject('OTP Verification');
        // });

        return ['status' => 'success', 'message' => 'User verified'];
    }

    public function verifyOTP(array $data)
    {
        $user = ApiUser::where('email', $data['email'])->first();

        if (!$user) {
            return ['status' => 'error', 'message' => 'User not found'];
        }

        if ($user->otp != $data['otp']) {
            return ['status' => 'error', 'message' => 'Invalid OTP'];
        }

        // Mark the user as verified and delete the OTP
        $user->verified = true;
        $user->otp = null;
        $user->save();

        return ['status' => 'success', 'message' => 'User verified'];
    }
}