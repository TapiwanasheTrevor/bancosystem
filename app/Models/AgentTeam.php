<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentTeam extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'team_lead_id',
        'description',
        'formed_date',
        'is_active',
    ];
    
    protected $casts = [
        'formed_date' => 'date',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the team leader
     */
    public function teamLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }
    
    /**
     * Get the team members
     */
    public function members(): HasMany
    {
        return $this->hasMany(AgentTeamMember::class, 'team_id');
    }
    
    /**
     * Get active team members
     */
    public function activeMembers()
    {
        return $this->members()->where('is_active', true);
    }
    
    /**
     * Get all users who are members of this team
     */
    public function users()
    {
        return User::whereHas('teams', function($query) {
            $query->where('agent_team_id', $this->id);
        });
    }
    
    /**
     * Get all users who are active members of this team
     */
    public function activeUsers()
    {
        return User::whereHas('teams', function($query) {
            $query->where('agent_team_id', $this->id)
                ->where('is_active', true);
        });
    }
    
    /**
     * Get the team's total commissions
     */
    public function getTotalCommissions()
    {
        $memberIds = $this->activeMembers()->pluck('user_id')->toArray();
        
        return Commission::whereIn('agent_id', $memberIds)
            ->where('status', 'paid')
            ->sum('commission_amount');
    }
    
    /**
     * Get the total number of members in the team
     */
    public function getMembersCount()
    {
        return $this->activeMembers()->count();
    }
}
