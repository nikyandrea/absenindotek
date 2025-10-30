<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\MonthlyReportService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonthlyReportController extends Controller
{
    protected $reportService;
    protected $auditService;

    public function __construct(MonthlyReportService $reportService, AuditLogService $auditService)
    {
        $this->reportService = $reportService;
        $this->auditService = $auditService;
    }

    /**
     * Get monthly report for a user
     */
    public function show(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $report = $this->reportService->generateMonthlyReport(
                $userId,
                $request->year,
                $request->month
            );

            $this->auditService->log(
                'monthly_report_view',
                'MonthlyReport',
                null,
                null,
                [
                    'user_id' => $userId,
                    'year' => $request->year,
                    'month' => $request->month,
                    'action' => 'View monthly report'
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export monthly report to Excel format (array)
     */
    public function export(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $data = $this->reportService->exportToArray(
                $userId,
                $request->year,
                $request->month
            );

            $this->auditService->log(
                'monthly_report_export',
                'MonthlyReport',
                null,
                null,
                [
                    'user_id' => $userId,
                    'year' => $request->year,
                    'month' => $request->month,
                    'action' => 'Export monthly report'
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export laporan: ' . $e->getMessage()
            ], 500);
        }
    }
}
