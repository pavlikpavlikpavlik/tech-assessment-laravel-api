<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Contracts\Foundation\Application as ContractsFoundationApplication;

/**
 * ViewGeneratedTokenController class
 */
class ViewGeneratedTokenController
{
    /**
     * Generate token and view function
     */
    public function generate(): Factory|FoundationApplication|View|ContractsFoundationApplication
    {
        $user = User::find(1);
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        $token = strstr($token, '|', false);
        $token = ltrim($token, '|');
        $token = 'Bearer ' . $token;

        return view('token', ['token' => $token]);
    }
}
