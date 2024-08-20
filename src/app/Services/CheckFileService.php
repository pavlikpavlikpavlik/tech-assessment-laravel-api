<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * CheckFileService class
 */
class CheckFileService
{
    /**
     * Wrong structure property
     */
    const WRONG_CASE_STRUCTURE = 'structure';

    /**
     * Wrong fullness property
     */
    const WRONG_CASE_FULLNESS = 'fullness';

    /**
     * Check file params function
     */
    public function checkFileParams(Request $request): array
    {
        $result = [
            'status' => true,
            'error' => null,
            'file' => [],
        ];

        if ($request->file()) {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:2048'
            ]);

            if ($validator->fails()) {
                $result['status'] = false;
                $result['error'] = $validator->errors();

                return $result;
            }

            $file = $request->file('file');
            $fileType = $file->getMimeType();

            if ($fileType !== 'application/json') {
                $result['status'] = false;
                $result['error'] = 'File is not a valid JSON.';

                return $result;
            }
        } else {
            $result['status'] = false;
            $result['error'] = 'No file was uploaded.';

            return $result;
        }

        $file = $this->parseFile($request->file('file'));

        if (!$file) {
            $result['status'] = false;
            $result['error'] = 'Critical error: invalid file structure';

            return $result;
        }

        $checkFileResult = $this->checkFileContent($file);

        if (!$checkFileResult['status']) {
            switch ($checkFileResult['case']) {
                case self::WRONG_CASE_STRUCTURE:
                    $result['status'] = false;
                    $result['error'] = 'One or more elements were not found, file probably has invalid structure.';

                    return $result;
                case self::WRONG_CASE_FULLNESS:
                    $result['status'] = false;
                    $result['error'] = 'One or more elements are not filled in.';

                    return $result;
            }
        }

        $result['file'] = $file;

        return $result;
    }

    /**
     * Check file content function
     */
    private function checkFileContent(array $data): array
    {
        $result = [
            'status' => true,
            'case' => ''
        ];

        if (!isset($data['data']) ||
            !isset($data['data']['id']) ||
            !isset($data['data']['name']) ||
            !isset($data['data']['recipient']) ||
            !isset($data['data']['recipient']['name']) ||
            !isset($data['data']['recipient']['email']) ||
            !isset($data['data']['issuer']) ||
            !isset($data['data']['issuer']['name']) ||
            !isset($data['data']['issuer']['identityProof']) ||
            !isset($data['data']['issuer']['identityProof']['type']) ||
            !isset($data['data']['issuer']['identityProof']['key']) ||
            !isset($data['data']['issuer']['identityProof']['location']) ||
            !isset($data['data']['issued']) ||
            !isset($data['signature']) ||
            !isset($data['signature']['type']) ||
            !isset($data['signature']['targetHash'])
        ) {
            $result['status'] = false;
            $result['case'] = self::WRONG_CASE_STRUCTURE;
            return $result;
        }

        if (empty($data['data']['id']) ||
            empty($data['data']['name']) ||
            empty($data['data']['issuer']['name']) ||
            empty($data['data']['issued']) ||
            empty($data['signature']['type'])
        ) {
            $result['status'] = false;
            $result['case'] = self::WRONG_CASE_FULLNESS;
            return $result;
        }

        return $result;
    }

    /**
     * Parse file function
     */
    public function parseFile($file): mixed
    {
        return json_decode(file_get_contents($file->getRealPath()), true);
    }
}
