<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\VerificationResult;
use App\Services\SaveVerificationResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SaveVerificationResultServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaveVerificationResultService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SaveVerificationResultService();
    }

    public function test_save_result_logs_error_on_exception(): void
    {
        $userId = 'test_user';
        $fileType = 'JSON';
        $result = 'success';

        $verificationResultMock = Mockery::mock(VerificationResult::class)->makePartial();
        $verificationResultMock->shouldReceive('create');

        $this->app->instance(VerificationResult::class, $verificationResultMock);

        Log::shouldReceive('error')
            ->with(
                'Verification result error: Database error',
                Mockery::on(function ($context) use ($userId, $fileType, $result) {
                    return isset($context['data']) &&
                        $context['data'] === [
                            'user_id' => $userId,
                            'file_type' => $fileType,
                            'result' => $result,
                        ] &&
                        isset($context['exception']);
                })
            );

        $service = new SaveVerificationResultService();
        $service->saveResult($userId, $fileType, $result);
        $this->assertTrue(true);
    }
}
