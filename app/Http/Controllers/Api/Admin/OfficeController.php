<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get all offices with employee count
     */
    public function index(Request $request)
    {
        try {
            $query = Office::withCount('users');

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $offices = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $offices
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kantor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get office detail
     */
    public function show($id)
    {
        try {
            $office = Office::with('users')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $office
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kantor tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Create new office
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'required|integer|min:10|max:5000', // in meters
                'timezone' => 'nullable|string|max:50',
                'is_active' => 'boolean'
            ]);

            // Map radius to radius_meters for database
            $data = $validated;
            $data['radius_meters'] = $validated['radius'];
            unset($data['radius']);

            $office = Office::create($data);

            // Audit log
            $this->auditLogService->log(
                'office_created',
                'offices',
                $office->id,
                null,
                $office->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Kantor berhasil ditambahkan',
                'data' => $office
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kantor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update office
     */
    public function update(Request $request, $id)
    {
        try {
            $office = Office::findOrFail($id);
            $oldData = $office->toArray();

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'address' => 'sometimes|required|string',
                'latitude' => 'sometimes|required|numeric|between:-90,90',
                'longitude' => 'sometimes|required|numeric|between:-180,180',
                'radius' => 'sometimes|required|integer|min:10|max:5000',
                'timezone' => 'nullable|string|max:50',
                'is_active' => 'sometimes|boolean'
            ]);

            // Map radius to radius_meters for database
            $data = $validated;
            if (isset($validated['radius'])) {
                $data['radius_meters'] = $validated['radius'];
                unset($data['radius']);
            }

            $office->update($data);

            // Audit log
            $this->auditLogService->log(
                'office_updated',
                'offices',
                $office->id,
                $oldData,
                $office->fresh()->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Kantor berhasil diupdate',
                'data' => $office
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate kantor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete office (soft delete - set inactive)
     */
    public function destroy($id)
    {
        try {
            $office = Office::findOrFail($id);

            // Check if office has active employees
            $activeEmployees = $office->users()->where('is_active', true)->count();
            if ($activeEmployees > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menghapus kantor. Masih ada {$activeEmployees} karyawan aktif."
                ], 400);
            }

            $oldData = $office->toArray();
            
            // Soft delete - set inactive instead of actual delete
            $office->is_active = false;
            $office->save();

            // Audit log
            $this->auditLogService->log(
                'office_deactivated',
                'offices',
                $office->id,
                $oldData,
                $office->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Kantor berhasil dinonaktifkan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kantor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle office active status
     */
    public function toggleActive($id)
    {
        try {
            $office = Office::findOrFail($id);
            $oldData = $office->toArray();

            $office->is_active = !$office->is_active;
            $office->save();

            // Audit log
            $this->auditLogService->log(
                $office->is_active ? 'office_activated' : 'office_deactivated',
                'offices',
                $office->id,
                $oldData,
                $office->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Status kantor berhasil diubah',
                'data' => $office
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status kantor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test geofence - check if coordinates are within office radius
     */
    public function testGeofence(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180'
            ]);

            $office = Office::findOrFail($id);

            // Calculate distance using Haversine formula
            $earthRadius = 6371000; // meters
            $lat1 = deg2rad($office->latitude);
            $lon1 = deg2rad($office->longitude);
            $lat2 = deg2rad($validated['latitude']);
            $lon2 = deg2rad($validated['longitude']);

            $dLat = $lat2 - $lat1;
            $dLon = $lon2 - $lon1;

            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos($lat1) * cos($lat2) *
                 sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            $withinGeofence = $distance <= $office->radius;

            return response()->json([
                'success' => true,
                'data' => [
                    'within_geofence' => $withinGeofence,
                    'distance' => round($distance, 2),
                    'allowed_radius' => $office->radius,
                    'office_location' => [
                        'latitude' => $office->latitude,
                        'longitude' => $office->longitude
                    ],
                    'test_location' => [
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude']
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal test geofence: ' . $e->getMessage()
            ], 500);
        }
    }
}
