<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    // Login with username and password
    public function loginWithUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Attach the token to the user object
        $user->token = $token;

        return response()->json(['user' => $user], 200);
    }

    // Login with phone number and OTP
    public function loginWithPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || $user->otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP'], 401);
        }

        // Clear OTP after successful login
        $user->otp = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Attach the token to the user object
        $user->token = $token;

        return response()->json(['user' => $user], 200);
    }

    // Generate OTP for phone login
    public function generateOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Generate a 6-character OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp = $otp;
        $user->save();

        // Send OTP to the user's phone (implement SMS sending logic here)
        // Example: SmsService::send($user->phone, "Your OTP is $otp");

        return response()->json(['message' => 'OTP sent successfully'], 200);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if OTP already exists
        if (!$user->otp) {
            // Generate a new 6-character OTP if not already generated
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp = $otp;
            $user->save();
        }

        // Send OTP to the user's phone (implement SMS sending logic here)
        // Example: SmsService::send($user->phone, "Your OTP is $user->otp");

        return response()->json(['message' => 'OTP sent successfully.'], 200);
    }

    public function confirmPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || $user->otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP'], 401);
        }

        // Confirm the phone number
        $user->otp = null; // Clear the OTP
        $user->phone_verified_at = now(); // Mark phone as verified
        $user->save();

        return response()->json(['message' => 'Phone number confirmed successfully.'], 200);
    }
}
