<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allowance extends Model
{
    protected $fillable = [
        'agent_id',
        'amount',
        'period',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public static function generateForPeriod(string $period): array
    {
        // Get all field agents and supervisors
        $agents = Agent::whereIn('type', ['field', 'supervisor'])
            ->where('is_active', true)
            ->get();

        $allowances = [];
        $workingDays = self::getWorkingDaysInPeriod($period);

        foreach ($agents as $agent) {
            $amount = match($agent->type) {
                'field' => $workingDays * 2, // $2 per day
                'supervisor' => $workingDays * 3, // $3 per day
                default => 0
            };

            if ($amount > 0) {
                $allowances[] = self::create([
                    'agent_id' => $agent->id,
                    'amount' => $amount,
                    'period' => $period,
                    'status' => 'pending'
                ]);
            }
        }

        return $allowances;
    }

    public static function calculateForPeriod(string $period): array
    {
        $allowances = self::where('period', $period)
            ->with('agent')
            ->get()
            ->groupBy('agent_id');

        $summary = [];

        foreach ($allowances as $agentId => $agentAllowances) {
            $agent = $agentAllowances->first()->agent;
            $totalAmount = $agentAllowances->sum('amount');

            $summary[] = [
                'agent_id' => $agentId,
                'agent_name' => $agent->name,
                'agent_type' => $agent->type,
                'total_amount' => $totalAmount,
                'status' => $agentAllowances->first()->status
            ];
        }

        return $summary;
    }

    private static function getWorkingDaysInPeriod(string $period): int
    {
        [$year, $month] = explode('-', $period);
        $startDate = \Carbon\Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $workingDays = 0;
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            // Count Monday through Friday
            if ($currentDate->dayOfWeek !== 0 && $currentDate->dayOfWeek !== 6) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    public function approve(): void
    {
        $this->status = 'approved';
        $this->save();
    }

    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->save();
    }
}
