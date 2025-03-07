<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DirectorLink extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'form_data' => 'array',
        'business_details' => 'array',
        'is_final_director' => 'boolean',
        'is_completed' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a unique token for the director link
     * 
     * @return string
     */
    public static function generateUniqueToken()
    {
        $token = Str::random(32);
        
        // Ensure token is unique
        while (self::where('token', $token)->exists()) {
            $token = Str::random(32);
        }
        
        return $token;
    }

    /**
     * Check if the link is expired
     * 
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if this is the last director in sequence
     * 
     * @return bool
     */
    public function isLastDirector()
    {
        return $this->director_position === $this->total_directors;
    }
}
