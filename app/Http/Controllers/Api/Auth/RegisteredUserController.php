<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // 1. Validate payload
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Create user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Fire Registered event (nếu có listener mailing)
//        event(new Registered($user));
        $user->sendEmailVerificationNotification();
        // 4. (Tuỳ chọn) Tự động login và tạo token Passport
        $token = $user->createToken('Laravel')->accessToken;
        Log::debug('$token: '.$token);
        // 5. Trả về JSON cho frontend
        return response()->json([
            'user'          => $user,
            'access_token'  => $token,
            'token_type'    => 'Bearer',
        ], 201);
    }
}
