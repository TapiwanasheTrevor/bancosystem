<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'application_number',
        'amount',
        'percentage',
        'period',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2'
    ];

    /**
     * Get the agent who earned this commission
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the form this commission is related to
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the product this commission is for
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who approved this commission
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the payment items for this commission
     */
    public function paymentItems(): HasMany
    {
        return $this->hasMany(CommissionPaymentItem::class);
    }

    /**
     * Calculate commission amount
     */
    public function calculateCommission(): void
    {
        $this->amount = $this->base_price * ($this->percentage / 100);
        $this->save();
    }

    /**
     * Approve the commission
     */
    public function approve(): void
    {
        $this->status = 'approved';
        $this->save();
    }

    /**
     * Mark the commission as paid
     */
    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->save();
    }

    /**
     * Reject the commission
     */
    public function reject(int $approverId, string $reason = null): void
    {
        $this->status = 'rejected';
        $this->approved_by = $approverId;
        $this->approval_date = now();

        if ($reason) {
            $this->notes = $reason;
        }

        $this->save();
    }

    /**
     * Scope query to only include pending commissions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope query to only include approved commissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope query to only include paid commissions
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope query to only include rejected commissions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope query to only include commissions for a specific agent
     */
    public function scopeForAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope query to only include commissions for a specific period
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    public static function calculateForPeriod(string $period): array
    {
        $commissions = self::where('period', $period)
            ->with('agent')
            ->get()
            ->groupBy('agent_id');

        $summary = [];

        foreach ($commissions as $agentId => $agentCommissions) {
            $agent = $agentCommissions->first()->agent;
            $totalAmount = $agentCommissions->sum('amount');
            $totalApplications = $agentCommissions->count();

            // We no longer have supervisor type, so no supervisor incentive calculation is needed
            $supervisorIncentive = 0;

            $summary[] = [
                'agent_id' => $agentId,
                'agent_name' => $agent->name,
                'agent_type' => $agent->type,
                'total_amount' => $totalAmount,
                'total_applications' => $totalApplications,
                'supervisor_incentive' => $supervisorIncentive,
                'grand_total' => $totalAmount + $supervisorIncentive
            ];
        }

        return $summary;
    }

    public static function generateForApplication(string $applicationNumber, float $basePrice, Agent $agent): self
    {
        $amount = $basePrice * ($agent->commission_rate / 100);
        $period = date('Y-m');

        return self::create([
            'agent_id' => $agent->id,
            'application_number' => $applicationNumber,
            'amount' => $amount,
            'percentage' => $agent->commission_rate,
            'period' => $period,
            'status' => 'pending'
        ]);
    }
}
