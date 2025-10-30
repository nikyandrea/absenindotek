<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get all users with filters
     */
    public function index(Request $request)
    {
        try {
            $query = User::with('office');

            // Filters
            if ($request->has('office_id')) {
                $query->where('office_id', $request->office_id);
            }

            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            if ($request->has('work_time_type')) {
                $query->where('work_time_type', $request->work_time_type);
            }

            if ($request->has('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $users = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'office_id' => $user->office_id,
                        'office_name' => $user->office->name,
                        'role' => $user->role,
                        'work_time_type' => $user->work_time_type,
                        'ontime_incentive' => $user->ontime_incentive,
                        'out_of_town_incentive' => $user->out_of_town_incentive,
                        'holiday_incentive' => $user->holiday_incentive,
                        'overtime_rate_per_hour' => $user->overtime_rate_per_hour,
                        'annual_leave_quota' => $user->annual_leave_quota,
                        'is_active' => $user->is_active,
                        'created_at' => $user->created_at,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single user
     */
    public function show($id)
    {
        try {
            $user = User::with('office')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'office_id' => $user->office_id,
                    'office' => [
                        'id' => $user->office->id,
                        'name' => $user->office->name,
                    ],
                    'role' => $user->role,
                    'work_time_type' => $user->work_time_type,
                    'ontime_incentive' => $user->ontime_incentive,
                    'out_of_town_incentive' => $user->out_of_town_incentive,
                    'holiday_incentive' => $user->holiday_incentive,
                    'overtime_rate_per_hour' => $user->overtime_rate_per_hour,
                    'annual_leave_quota' => $user->annual_leave_quota,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new user
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            // Log creation
            $this->auditLogService->logCreate('user', $user->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $before = $user->toArray();

            $data = $request->validated();
            
            // Debug log
            \Log::info('Update user request data:', $data);

            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);
            
            // Debug log after update
            $userAfter = $user->fresh();
            \Log::info('User after update:', [
                'ontime_incentive_per_day' => $userAfter->ontime_incentive_per_day,
                'out_of_town_incentive_per_day' => $userAfter->out_of_town_incentive_per_day,
                'holiday_incentive_per_day' => $userAfter->holiday_incentive_per_day,
            ]);

            // Log update
            $this->auditLogService->logUpdate('user', $user->id, $before, $userAfter->toArray());

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            \Log::error('Update user error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting yourself
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun Anda sendiri'
                ], 403);
            }

            $userData = $user->toArray();
            $user->delete();

            // Log deletion
            $this->auditLogService->logDelete('user', $id, $userData);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleActive($id)
    {
        try {
            $user = User::findOrFail($id);


            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menonaktifkan akun Anda sendiri'
                ], 403);
            }

            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'aktif' : 'nonaktif';

            return response()->json([
                'success' => true,
                'message' => "User berhasil di{$status}kan",
                'data' => ['is_active' => $user->is_active]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
