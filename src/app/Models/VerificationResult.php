<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * VerificationResult class
 */
class VerificationResult extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'verification_results';

    /**
     * Fillable fields
     */
    protected $fillable = [
        'user_id',
        'file_type',
        'result'
    ];
}
