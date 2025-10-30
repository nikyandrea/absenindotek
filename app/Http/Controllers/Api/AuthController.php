<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Login user and generate token
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif'
                ], 403);
            }

            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Log the login
            $this->auditLogService->log(
                'login',
                'auth',
                $user->id,
                null,
                ['user' => $user->name, 'email' => $user->email]
            );

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'office_id' => $user->office_id,
                        'work_time_type' => $user->work_time_type,
                    ],
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Log the logout
            $this->auditLogService->log(
                'logout',
                'auth',
                $request->user()->id,
                null,
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user()->load('office');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'work_time_type' => $user->work_time_type,
                    'office' => [
                        'id' => $user->office->id,
                        'name' => $user->office->name,
                        'latitude' => $user->office->latitude,
                        'longitude' => $user->office->longitude,
                    ],
                    'ontime_incentive' => $user->ontime_incentive,
                    'out_of_town_incentive' => $user->out_of_town_incentive,
                    'holiday_incentive' => $user->holiday_incentive,
                    'is_active' => $user->is_active,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
