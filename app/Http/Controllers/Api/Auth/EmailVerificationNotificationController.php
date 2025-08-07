<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message'  => 'Email already verified.',
                'status'   => 'already_verified',
                'verified' => true,
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message'  => 'Verification link sent.',
            'status'   => 'verification-link-sent',
            'verified' => false,
        ], 200);
    }
}
