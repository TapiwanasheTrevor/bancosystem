<?php

namespace App\Http\Controllers;

use App\Models\AgentTeam;
use App\Models\AgentTeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Display a listing of teams
     */
    public function index()
    {
        $teams = AgentTeam::with(['teamLead', 'members.user'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new team
     */
    public function create()
    {
        $agents = User::where('role', 'agent')
            ->where('status', 1)
            ->where('is_team_lead', false)
            ->doesntHave('teams')
            ->orderBy('name')
            ->get();
            
        return view('admin.teams.create', compact('agents'));
    }

    /**
     * Store a newly created team
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'team_lead_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'formed_date' => 'required|date',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        try {
            // Create the team
            $team = new AgentTeam();
            $team->name = $request->input('name');
            $team->team_lead_id = $request->input('team_lead_id');
            $team->description = $request->input('description');
            $team->formed_date = $request->input('formed_date');
            $team->is_active = true;
            $team->save();
            
            // Update the team lead user
            $teamLead = User::find($request->input('team_lead_id'));
            $teamLead->is_team_lead = true;
            $teamLead->team_joined_date = $request->input('formed_date');
            $teamLead->save();
            
            // Add the team lead as a member of their own team
            $teamLeadMember = new AgentTeamMember();
            $teamLeadMember->team_id = $team->id;
            $teamLeadMember->user_id = $teamLead->id;
            $teamLeadMember->joined_date = $request->input('formed_date');
            $teamLeadMember->is_active = true;
            $teamLeadMember->save();
            
            // Add other members if any
            if ($request->has('members')) {
                foreach ($request->input('members') as $memberId) {
                    $member = new AgentTeamMember();
                    $member->team_id = $team->id;
                    $member->user_id = $memberId;
                    $member->joined_date = $request->input('formed_date');
                    $member->is_active = true;
                    $member->save();
                    
                    // Update the user record
                    $user = User::find($memberId);
                    $user->supervisor_id = $teamLead->id;
                    $user->team_joined_date = $request->input('formed_date');
                    $user->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('teams.show', $team->id)
                ->with('success', 'Team created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating team: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified team
     */
    public function show(string $id)
    {
        $team = AgentTeam::with(['teamLead', 'members.user'])->findOrFail($id);
        
        // Get members who are still active in the team
        $activeMembers = $team->members()
            ->where('is_active', true)
            ->get();
            
        // Get team performance metrics
        $totalCommissions = 0;
        $memberPerformance = [];
        
        foreach ($activeMembers as $member) {
            $user = $member->user;
            $commissions = $user->commissions()->where('status', 'paid')->sum('commission_amount');
            $totalCommissions += $commissions;
            
            $memberPerformance[$user->id] = [
                'name' => $user->name,
                'commissions' => $commissions,
                'referrals' => $user->referral_count
            ];
        }
        
        // Get available agents who could be added to this team
        $availableAgents = User::where('role', 'agent')
            ->where('status', 1)
            ->where('is_team_lead', false)
            ->whereDoesntHave('teams', function($query) use ($id) {
                $query->where('agent_team_id', $id)
                    ->where('is_active', true);
            })
            ->orderBy('name')
            ->get();
            
        return view('admin.teams.show', compact('team', 'activeMembers', 'totalCommissions', 'memberPerformance', 'availableAgents'));
    }

    /**
     * Show the form for editing the team
     */
    public function edit(string $id)
    {
        $team = AgentTeam::findOrFail($id);
        
        return view('admin.teams.edit', compact('team'));
    }

    /**
     * Update the specified team
     */
    public function update(Request $request, string $id)
    {
        $team = AgentTeam::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $team->name = $request->input('name');
        $team->description = $request->input('description');
        $team->is_active = $request->input('is_active', false);
        $team->save();
        
        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Add a member to the team
     */
    public function addMember(Request $request, string $id)
    {
        $team = AgentTeam::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'joined_date' => 'required|date'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $userId = $request->input('user_id');
        
        // Check if the member is already in the team
        $existingMember = AgentTeamMember::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->first();
            
        if ($existingMember) {
            if ($existingMember->is_active) {
                return redirect()->back()
                    ->with('error', 'User is already a member of this team.');
            } else {
                // Reactivate the member
                $existingMember->is_active = true;
                $existingMember->joined_date = $request->input('joined_date');
                $existingMember->left_date = null;
                $existingMember->save();
                
                // Update user record
                $user = User::find($userId);
                $user->supervisor_id = $team->team_lead_id;
                $user->team_joined_date = $request->input('joined_date');
                $user->save();
                
                return redirect()->route('teams.show', $team->id)
                    ->with('success', 'Member reactivated successfully.');
            }
        }
        
        // Add new member
        $member = new AgentTeamMember();
        $member->team_id = $team->id;
        $member->user_id = $userId;
        $member->joined_date = $request->input('joined_date');
        $member->is_active = true;
        $member->save();
        
        // Update user record
        $user = User::find($userId);
        $user->supervisor_id = $team->team_lead_id;
        $user->team_joined_date = $request->input('joined_date');
        $user->save();
        
        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Member added successfully.');
    }

    /**
     * Remove a member from the team
     */
    public function removeMember(Request $request, string $id)
    {
        $member = AgentTeamMember::findOrFail($id);
        
        // Cannot remove the team lead from their own team
        $team = $member->team;
        if ($member->user_id === $team->team_lead_id) {
            return redirect()->back()
                ->with('error', 'Cannot remove team lead from their own team.');
        }
        
        $validator = Validator::make($request->all(), [
            'left_date' => 'required|date'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Mark the member as inactive
        $member->is_active = false;
        $member->left_date = $request->input('left_date');
        $member->save();
        
        // Update user record
        $user = $member->user;
        $user->supervisor_id = null;
        $user->save();
        
        return redirect()->route('teams.show', $member->team_id)
            ->with('success', 'Member removed successfully.');
    }
}
