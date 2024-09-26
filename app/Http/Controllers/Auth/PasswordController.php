<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // Check if the user has a password
        $user = $request->user();
        $hasPassword = !is_null($user->password);

        // Validation rules based on whether the user has a password
        $rules = [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];

        if ($hasPassword) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        // Validate the request
        $validated = $request->validateWithBag('updatePassword', $rules);

        // Update the password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
