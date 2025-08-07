<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class AuthorizationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = [
            'client_id'     => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'grant_type'    => 'password',
            'username'      => $request->input('email'),
            'password'      => $request->input('password'),
            'scope'         => ''
        ];
        $token = $this->makeRequest($credentials);
        return response()->json($token);
    }

    /**
     * @throws AuthenticationException
     * @throws ConnectionException
     */
    private function makeRequest(array $credentials)
    {
        Log::debug('Sending credentials', $credentials);
        $response = Http::acceptJson()
            ->post(config('app.url') . '/api/oauth/token', $credentials);

        if ($response->failed()) {
            Log::error('OAuth token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new AuthenticationException('Unauthenticated.');
        }

        return $response->json();
    }


    public function destroy(Request $request): JsonResponse
    {
        /** @var Token $token */
        $token = $request->user()->token();
        RefreshToken::query()
            ->where('access_token_id', $token->oauth_access_token_id)
            ->update(['revoked' => true]);

        // Thu hồi access token
        $token->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }
}
