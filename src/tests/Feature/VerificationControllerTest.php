<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\VerificationController;
use App\Services\CheckFileService;
use App\Services\SaveVerificationResultService;
use App\Services\VerifyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class VerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private VerificationController $controller;
    private VerifyService $verifyService;
    private SaveVerificationResultService $saveVerificationResultService;
    private CheckFileService $checkFileService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifyService = Mockery::mock(VerifyService::class);
        $this->saveVerificationResultService = Mockery::mock(SaveVerificationResultService::class);
        $this->checkFileService = Mockery::mock(CheckFileService::class);

        $this->controller = new VerificationController(
            $this->verifyService,
            $this->saveVerificationResultService,
            $this->checkFileService
        );
    }

    public function test_verify_returns_error_if_file_check_fails(): void
    {
        $request = Request::create('/verify', 'POST');
        $this->checkFileService->shouldReceive('checkFileParams')
            ->once()
            ->with($request)
            ->andReturn(['status' => false, 'error' => 'Invalid file']);

        Log::shouldReceive('error')->once()->with('Invalid file');

        $response = $this->controller->verify($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Invalid file'], $response->getData(true));
    }

    public function test_verify_successfully_processes_file(): void
    {
        $request = Request::create('/verify', 'POST');
        $fileData = [
            'data' => [
                'id' => 'test_id',
                'issuer' => [
                    'name' => 'Test Issuer'
                ]
            ]
        ];

        $this->checkFileService->shouldReceive('checkFileParams')
            ->once()
            ->with($request)
            ->andReturn(['status' => true, 'file' => $fileData]);

        $this->verifyService->shouldReceive('verifyData')
            ->once()
            ->with($fileData)
            ->andReturn('success');

        $this->saveVerificationResultService->shouldReceive('saveResult')
            ->once()
            ->with('test_id', 'JSON', 'success');

        $response = $this->controller->verify($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'data' => [
                'issuer' => 'Test Issuer',
                'result' => 'success',
            ]
        ], $response->getData(true));
    }
}
