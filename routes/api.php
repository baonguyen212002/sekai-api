<?php

use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\Auth\EmailVerificationNotificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use App\Http\Controllers\Api\Auth\RegisteredUserController;
use App\Http\Controllers\Api\Auth\AuthorizationController;
use App\Http\Controllers\Api\User\UpdateProfileController;

Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);

Route::middleware('auth:api')->group(function () {
    // Lấy thông tin user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // Cập nhật profile
    Route::post('/update/user/{user}', [UpdateProfileController::class, 'update']);

    // Gửi lại email xác thực
    Route::post('/email/verification-notification',
        [EmailVerificationNotificationController::class, 'store']
    )
        ->middleware('throttle:6,1')
        ->name('verification.send');   // ← phải đặt tên đúng

    // Link verify email
    Route::get('/email/verify/{id}/{hash}',
        VerifyEmailController::class
    )
        ->middleware(['signed', 'auth:api'])
        ->name('verification.verify'); // ← phải đặt tên đúng

    // Logout
    Route::post('/logout', [AuthorizationController::class, 'destroy']);
});

// Register / Login không cần auth
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login',    [AuthorizationController::class, 'store']);

