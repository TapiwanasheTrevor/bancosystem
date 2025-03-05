<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample agents
        $agents = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'position' => 'field_agent',
                'phone_number' => '+26371234567',
                'status' => 1,
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'position' => 'office_agent',
                'phone_number' => '+26371234568',
                'status' => 1,
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'position' => 'supervisor',
                'phone_number' => '+26371234569',
                'status' => 1,
            ],
        ];

        foreach ($agents as $agentData) {
            // First check if the agent already exists
            $existingAgent = User::where('email', $agentData['email'])->first();
            
            if ($existingAgent) {
                // Update existing agent if needed
                $existingAgent->role = 'agent';
                $existingAgent->position = $agentData['position'];
                $existingAgent->status = 1;
                $existingAgent->save();
                
                // Generate referral code if not exists
                if (empty($existingAgent->referral_code)) {
                    $existingAgent->generateReferralCode();
                }
                
                $this->command->info("Updated agent: {$existingAgent->name} with referral code: {$existingAgent->referral_code}");
            } else {
                // Create new agent
                $agent = User::create($agentData);
                
                // Generate referral code for new agent
                $agent->generateReferralCode();
                
                $this->command->info("Created agent: {$agent->name} with referral code: {$agent->referral_code}");
            }
        }
    }
}
