<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\VerifyService;

class VerifyServiceTest extends TestCase
{
    protected VerifyService $verifyService;

    protected function setUp(): void
    {
        $this->verifyService = new VerifyService();
    }

    public function testExtractDataPathsWithSimpleArray()
    {
        $data = [
            'name' => 'John',
            'age' => 30,
        ];
        $hashes = [];
        $this->verifyService->extractDataPaths($data, '', $hashes);

        $this->assertCount(2, $hashes);
        $this->assertContains('name', $hashes);
        $this->assertContains('age', $hashes);
    }

    public function testExtractDataPathsWithNestedArray()
    {
        $data = [
            'user' => [
                'name' => 'John',
                'details' => [
                    'age' => 30,
                    'location' => 'USA',
                ],
            ],
        ];
        $hashes = [];
        $this->verifyService->extractDataPaths($data, '', $hashes);

        $this->assertCount(3, $hashes);
        $this->assertContains('user.name', $hashes);
        $this->assertContains('user.details.age', $hashes);
        $this->assertContains('user.details.location', $hashes);
    }

    public function testExtractDataPathsWithEmptyArray()
    {
        $data = [];
        $hashes = [];
        $this->verifyService->extractDataPaths($data, '', $hashes);

        $this->assertCount(0, $hashes);
    }

    public function testGetValueByPathWithExistingPath()
    {
        $data = [
            'user' => [
                'name' => 'John',
                'details' => [
                    'age' => 30,
                    'location' => 'USA',
                ],
            ],
        ];

        $result = $this->verifyService->getValueByPath($data, 'user.name');
        $this->assertEquals('John', $result);

        $result = $this->verifyService->getValueByPath($data, 'user.details.age');
        $this->assertEquals(30, $result);
    }

    public function testGetValueByPathWithNonExistingPath()
    {
        $data = [
            'user' => [
                'name' => 'John',
                'details' => [
                    'age' => 30,
                ],
            ],
        ];

        $result = $this->verifyService->getValueByPath($data, 'user.details.location');
        $this->assertEquals('', $result);
    }

    public function testGetValueByPathWithEmptyArray()
    {
        $data = [];
        $result = $this->verifyService->getValueByPath($data, 'user.name');
        $this->assertEquals('', $result);
    }
}

