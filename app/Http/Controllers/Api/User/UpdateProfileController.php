<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateProfileController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function update(User $user, Request $request)
    {
        DB::beginTransaction();
        try {
            Validator::make((array)$request, [
                'name' => ['required', 'string', 'max:255'],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
            ])->validateWithBag('updateProfileInformation');

            if ($request->input('email') !== $user->email) {
                $this->updateVerifiedUser($user, $request);
            } else {
                $user->forceFill([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                ])->save();
            }
            DB::commit();
            return $user->updateOrFail($request->only(['name', 'email']));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("User $user->id", [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function updateVerifiedUser(User $user, Request $request): void
    {
        $user->forceFill([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
