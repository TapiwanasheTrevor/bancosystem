<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'employee_number',
        'commission_rate',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commission_rate' => 'decimal:2'
    ];

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    public function calculateCommission($amount): float
    {
        return $amount * ($this->commission_rate / 100);
    }

    public function calculateAllowance($days): float
    {
        return match($this->type) {
            'field' => $days * 2, // $2 per day
            'supervisor' => $days * 3, // $3 per day
            default => 0
        };
    }

    public function calculateSupervisorIncentive($subordinateCommission): float
    {
        return $subordinateCommission * 0.10; // 10% of subordinate's commission
    }
}
