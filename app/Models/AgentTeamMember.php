<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentTeamMember extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'team_id',
        'user_id',
        'joined_date',
        'left_date',
        'is_active',
    ];
    
    protected $casts = [
        'joined_date' => 'date',
        'left_date' => 'date',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the team this member belongs to
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(AgentTeam::class, 'team_id');
    }
    
    /**
     * Get the user (agent) who is a member
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Calculate the member's tenure in days
     */
    public function getTenureDays(): int
    {
        $startDate = $this->joined_date;
        $endDate = $this->is_active ? now() : $this->left_date;
        
        if (!$endDate) {
            $endDate = now();
        }
        
        return $startDate->diffInDays($endDate);
    }
    
    /**
     * Check if this member is the team lead
     */
    public function isTeamLead(): bool
    {
        return $this->user_id === $this->team->team_lead_id;
    }
}
