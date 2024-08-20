<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\VerificationResult;
use Illuminate\Support\Facades\Log;
use \Exception;
/**
 * SaveVerificationResultService class
 */
class SaveVerificationResultService
{
    /**
     * Save result function
     */
    public function saveResult(string $userId, string $fileType, string $result): void
    {
        $data = [
            'user_id' => $userId,
            'file_type' => $fileType,
            'result' => $result
        ];

        try {
            VerificationResult::create($data);
        } catch (Exception $throwable) {
            Log::error('Verification result error: ' . $throwable->getMessage(), ['data' => $data, 'exception' => $throwable]);
        }
    }
}
