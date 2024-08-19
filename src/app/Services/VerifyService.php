<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * VerifyService class
 */
class VerifyService
{
    /**
     * Dns google url for sprintf
     */
    const DNS_GOOGLE_URL = 'https://dns.google/resolve?name=%s&type=TXT';

    /**
     * Check data
     */
    public function verifyData(array $data): string
    {
        if (!$this->isValidRecipient($data)) {
            return 'invalid_recipient';
        }

        if (!$this->isValidIssuer($data)) {
            return 'invalid_issuer';
        }

        if (!$this->isValidSignature($data)) {
            return 'invalid_signature';
        }

        return 'verified';
    }

    /**
     * Check recipient valid
     */
    private function isValidRecipient(array $data): bool
    {
        return isset($data['data']['recipient']['name']) &&
            isset($data['data']['recipient']['email']);
    }

    /**
     * Check the validity of the issuer.
     */
    private function isValidIssuer(array $data): bool
    {
        if (isset($data['data']['issuer'] )) {
            $issuer = $data['data']['issuer'];
        } else {
            Log::notice('One or more elements were not found, file probably has invalid structure.');
            return false;
        }

        if (!$issuer ||
            !isset($issuer['name']) ||
            !isset($issuer['identityProof']['key']) ||
            !isset($issuer['identityProof']['location'])
        ) {
            Log::notice('One or more elements were not found, file probably has invalid structure.');
            return false;
        }

        $dnsRecords = $this->getDnsTxtRecords($issuer['identityProof']['location']);

        return in_array($issuer['identityProof']['key'], $dnsRecords);
    }

    /**
     * Proves the validity of the signature.
     */
    private function isValidSignature(array $data): bool
    {
        $targetHash = '';
        $computedHash = '';
        $result = false;

        if (isset($data['signature']['targetHash'])) {
            $targetHash = $data['signature']['targetHash'];
        } else {
            Log::notice('One or more elements were not found, file probably has invalid structure.');
        }

        if (isset($data['data'])) {
            $computedHash = $this->computeTargetHash($data['data']);
        }

        if (!empty($targetHash) && !empty($computedHash)) {
            $result = $computedHash === $targetHash;
        }

        return $result;
    }

    /**
     * Get DNS TXT records for the specified domain.
     */
    private function getDnsTxtRecords(string $domain): array
    {
        $response = Http::get(sprintf(self::DNS_GOOGLE_URL, $domain));
        $body = $response->json();

        return array_column($body['Answer'] ?? [], 'data');
    }

    /**
     * Compute targetHash from JSON data.
     */
    private function computeTargetHash(array $data): string
    {
        $hashes = [];
        $this->extractDataPaths($data, '', $hashes);

        $hashedValues = array_map(function ($path) use ($data) {
            $value = $this->getValueByPath($data, $path);
            return hash('sha256', $value);
        }, $hashes);

        sort($hashedValues);
        $combinedHash = implode('', $hashedValues);

        return hash('sha256', $combinedHash);
    }

    /**
     * Recursively extract data paths in key.value format.
     */
    private function extractDataPaths(array $data, string $prefix, array &$hashes): void
    {
        foreach ($data as $key => $value) {
            $path = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->extractDataPaths($value, $path, $hashes);
            } else {
                $hashes[] = $path;
            }
        }
    }

    /**
     * Get the value from the path from the data.
     */
    private function getValueByPath(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return '';
            }
        }

        return $value;
    }
}
