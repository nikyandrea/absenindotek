<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaceProfile;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FaceController extends Controller
{
    protected $faceService;

    public function __construct(FaceRecognitionService $faceService)
    {
        $this->faceService = $faceService;
    }

    /**
     * Get user's face profile
     */
    public function getProfile()
    {
        $user = Auth::user();
        $profile = $user->faceProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Face profile tidak ditemukan. Silakan lakukan enrollment terlebih dahulu.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'has_profile' => true,
                'enrolled_at' => $profile->created_at,
                'liveness_threshold' => $profile->liveness_threshold
            ]
        ]);
    }

    /**
     * Enroll face (3-5 photos from different angles)
     */
    public function enroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photos' => 'required|array|min:3|max:5',
            'photos.*' => 'required|string', // base64 images
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Check if already has profile
            if ($user->faceProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki face profile. Hubungi HRD untuk reset.'
                ], 400);
            }

            // Process each photo and generate embeddings
            $embeddings = [];
            foreach ($request->photos as $photo) {
                // Check liveness
                $liveness = $this->faceService->checkLiveness($photo);
                if (!$liveness['is_live']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Liveness check gagal pada salah satu foto. Gunakan wajah asli.'
                    ], 400);
                }

                $embedding = $this->faceService->generateEmbedding($photo);
                $embeddings[] = $embedding;
            }

            // Average embeddings (simple approach) or store multiple
            // For now, we'll use the first one as primary
            $primaryEmbedding = $embeddings[0];

            // Create face profile
            DB::beginTransaction();
            try {
                $profile = FaceProfile::create([
                    'user_id' => $user->id,
                    'embedding' => $primaryEmbedding,
                    'liveness_threshold' => 0.85,
                    'match_threshold' => 0.85
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Face enrollment berhasil',
                    'data' => [
                        'enrolled_at' => $profile->created_at
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify face against stored profile
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|string', // base64 image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $profile = $user->faceProfile;

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face profile tidak ditemukan'
                ], 404);
            }

            // Check liveness
            $liveness = $this->faceService->checkLiveness($request->photo);
            if (!$liveness['is_live']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liveness check gagal'
                ], 400);
            }

            // Generate embedding from photo
            $currentEmbedding = $this->faceService->generateEmbedding($request->photo);

            // Verify against stored profile
            $verification = $this->faceService->verifyFace(
                $profile->embedding,
                $currentEmbedding,
                $profile->match_threshold
            );

            if ($verification['match']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Face verification berhasil',
                    'data' => [
                        'match' => true,
                        'score' => $verification['score']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Face verification gagal. Score: ' . $verification['score'],
                    'data' => [
                        'match' => false,
                        'score' => $verification['score']
                    ]
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
