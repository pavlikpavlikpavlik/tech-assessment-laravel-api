<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\VerifyService;
use App\Services\SaveVerificationResultService;
use Illuminate\Support\Facades\Log;
use App\Services\CheckFileService;

/**
 * VerificationController class
 */
class VerificationController extends Controller
{

    /**
     * VerificationController constructor
     */
    public function __construct(
        private readonly VerifyService $verifyService,
        private readonly SaveVerificationResultService $saveVerificationResultService,
        private readonly CheckFileService $checkFileService
    ) {}

    /**
     * Processes a request to validate a JSON file.
     */
    public function verify(Request $request): JsonResponse
    {
        $checkResult = $this->checkFileService->checkFileParams($request);

        if (!$checkResult['status']) {
            Log::error($checkResult['error']);
            return response()->json([
                'error' => $checkResult['error']
            ], 400);
        }

        $data = $checkResult['file'];

        $result = $this->verifyService->verifyData($data);

        $this->saveVerificationResultService->saveResult($data['data']['id'], 'JSON', $result);

        $issuerName = $data['data']['issuer']['name'];

        return response()->json([
            'data' => [
                'issuer' => $issuerName,
                'result' => $result,
            ]
        ], 200);
    }
}
