<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Notifications\SecurityEventNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Send failed login notification
            // $settings = Setting::first();
            // if ($settings && $settings->email) {
            //     $settings->notify(new SecurityEventNotification('failed_login', [
            //         'ip' => $request->ip(),
            //         'time' => now()->toDateTimeString(),
            //         'user_agent' => $request->header('User-Agent'),
            //     ]));
            // }

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $tokenName = substr($request->header('User-Agent') ?? 'admin-session', 0, 255);
        $token = $user->createToken($tokenName)->plainTextToken;

        // Send login notification
        // $settings = Setting::first();
        // if ($settings && $settings->email) {
        //     $settings->notify(new SecurityEventNotification('login', [
        //         'ip' => $request->ip(),
        //         'time' => now()->toDateTimeString(),
        //         'user_agent' => $request->header('User-Agent'),
        //     ]));
        // }

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function sessions(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();
        $tokens = $request->user()
            ->tokens()
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'abilities', 'last_used_at', 'created_at']);

        return response()->json([
            'data' => [
                'currentTokenId' => $currentToken?->id,
                'sessions' => $tokens,
                'twoFactorEnabled' => $request->user()->two_factor_enabled,
            ],
        ]);
    }

    public function revokeSession(Request $request, string $tokenId)
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (!$token) {
            return response()->json(['message' => 'Session not found'], 404);
        }

        if ($request->user()->currentAccessToken()?->id === $token->id) {
            return response()->json(['message' => 'Cannot revoke current active session from this device'], 400);
        }

        $token->delete();

        return response()->json([
            'message' => 'Session revoked successfully',
        ]);
    }

    public function toggleTwoFactor(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->two_factor_enabled = $request->boolean('enabled');
        $user->save();

        return response()->json([
            'message' => $user->two_factor_enabled ? 'Two-factor authentication enabled' : 'Two-factor authentication disabled',
            'twoFactorEnabled' => $user->two_factor_enabled,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->password = $request->password;
        $user->save();

        // Send password change notification
        // $settings = Setting::first();
        // if ($settings && $settings->email) {
        //     $settings->notify(new SecurityEventNotification('password_change', [
        //         'time' => now()->toDateTimeString(),
        //         'ip' => $request->ip(),
        //     ]));
        // }

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    public function logoutOtherDevices(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();

        if ($currentToken) {
            $request->user()->tokens()->where('id', '!=', $currentToken->id)->delete();
        } else {
            $request->user()->tokens()->delete();
        }

        return response()->json([
            'message' => 'All other sessions have been signed out successfully',
        ]);
    }
}
