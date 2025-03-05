<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position',
        'phone_number',
        'alternate_phone',
        'referral_code',
        'referred_by',
        'status',
        'referral_count',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
            'referral_count' => 'integer',
        ];
    }
    
    /**
     * Get customers referred by this agent
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }
    
    /**
     * Get the agent who referred this user
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
    
    /**
     * Get forms submitted by customers referred by this agent
     */
    public function referredForms()
    {
        // Return a relationship rather than a query builder
        return $this->hasMany(Form::class, 'referred_by');
    }
    
    /**
     * Generate a unique referral code for the user
     */
    public function generateReferralCode()
    {
        // Generate a unique referral code based on name and random characters
        $namePrefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->name), 0, 3));
        $uniqueCode = $namePrefix . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 7));
        
        $this->referral_code = $uniqueCode;
        $this->save();
        
        return $uniqueCode;
    }
    
    /**
     * Track a new referral for this agent
     */
    public function trackReferral()
    {
        $this->increment('referral_count');
        return $this->referral_count;
    }
    
    /**
     * Check if user is an agent
     */
    public function isAgent()
    {
        return $this->role === 'agent';
    }
    
    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === 1;
    }
    
    /**
     * Get documents uploaded by this agent
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'agent_id');
    }
    
    /**
     * Get documents for this user (when user is a client)
     */
    public function clientDocuments()
    {
        return $this->hasMany(Document::class, 'user_id');
    }
    
    /**
     * Get forms submitted by this user
     */
    public function forms()
    {
        return $this->hasMany(Form::class, 'user_id');
    }
    
    /**
     * Get forms assigned to this agent
     */
    public function assignedForms()
    {
        return $this->hasMany(Form::class, 'agent_id');
    }
    
    /**
     * Check if user is an admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
