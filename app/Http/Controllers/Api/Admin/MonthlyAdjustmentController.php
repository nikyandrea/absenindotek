<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyAdjustment;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonthlyAdjustmentController extends Controller
{
    protected $auditService;

    public function __construct(AuditLogService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Get adjustments for a user in specific period
     */
    public function index(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $adjustments = MonthlyAdjustment::with('creator')
            ->forPeriod($userId, $request->year, $request->month)
            ->orderBy('type')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $adjustments
        ]);
    }

    /**
     * Store a new adjustment
     */
    public function store(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'type' => 'required|in:deduction,incentive',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $adjustment = MonthlyAdjustment::create([
                'user_id' => $userId,
                'year' => $request->year,
                'month' => $request->month,
                'type' => $request->type,
                'name' => $request->name,
                'amount' => $request->amount,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            $this->auditService->log(
                'monthly_adjustment_create',
                'MonthlyAdjustment',
                $adjustment->id,
                null,
                $adjustment->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Adjustment berhasil ditambahkan',
                'data' => $adjustment->load('creator')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an adjustment
     */
    public function update(Request $request, $userId, $id)
    {
        $adjustment = MonthlyAdjustment::where('user_id', $userId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $oldData = $adjustment->toArray();
            $adjustment->update($request->only(['name', 'amount', 'notes']));

            $this->auditService->log(
                'monthly_adjustment_update',
                'MonthlyAdjustment',
                $adjustment->id,
                $oldData,
                $adjustment->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Adjustment berhasil diupdate',
                'data' => $adjustment->load('creator')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an adjustment
     */
    public function destroy($userId, $id)
    {
        $adjustment = MonthlyAdjustment::where('user_id', $userId)->findOrFail($id);

        try {
            $adjustmentData = $adjustment->toArray();
            $adjustment->delete();

            $this->auditService->log(
                'monthly_adjustment_delete',
                'MonthlyAdjustment',
                $id,
                $adjustmentData,
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'Adjustment berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus adjustment: ' . $e->getMessage()
            ], 500);
        }
    }
}
