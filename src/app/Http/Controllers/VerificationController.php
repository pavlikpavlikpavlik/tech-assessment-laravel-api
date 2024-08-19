<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\VerifyService;
use App\Services\SaveVerificationResultService;
use Illuminate\Support\Facades\Log;

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
        private readonly SaveVerificationResultService $saveVerificationResultService
    ) {}

    /**
     * Processes a request to validate a JSON file.
     */
    public function verify(Request $request): JsonResponse
    {
        if ($request->file()) {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:2048'
            ]);

            if ($validator->fails()) {
                Log::info('Maximum file size exceeded (more than 2MB).');
                return response()->json([
                    'error' => $validator->errors() . ' Maximum file size exceeded (more than 2MB).'
                ], 400);
            }

            $file = $request->file('file');
            $fileType = $file->getMimeType();

            if ($fileType !== 'application/json') {
                Log::info('File is not a valid JSON.');
                return response()->json([
                    'error' => 'File is not a valid JSON.'
                ], 400);
            }

            $data = $this->parseFile($request);
        } else {
            Log::info('Request without file.');
            return response()->json([
                'error' => 'No file was uploaded.'
            ], 400);
        }

        $result = $this->verifyService->verifyData($data);

        if (isset($data['data']['issuer']['name'])) {
            $issuerName = $data['data']['issuer']['name'];
        } else {
            Log::notice('One or more elements were not found, file probably has invalid structure.');

            return response()->json([
                'error' => 'One or more elements were not found, file probably has invalid structure.'
            ], 400);
        }

        $this->saveVerificationResultService->saveResult($data['data']['id'], $fileType, $result);

        return response()->json([
            'data' => [
                'issuer' => $issuerName,
                'result' => $result,
            ]
        ], 200);
    }

    /**
     * Parse file function
     */
    public function parseFile(Request $request): mixed
    {
        $file = $request->file('file');

        return json_decode(file_get_contents($file->getRealPath()), true);
    }
}
