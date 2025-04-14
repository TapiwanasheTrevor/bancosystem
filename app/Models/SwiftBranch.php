<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwiftBranch extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'branch_name',
        'branch_code',
        'province',
        'district',
        'address',
        'contact_person',
        'contact_number',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get all branches in a specific province
     * 
     * @param string $province
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByProvince(string $province)
    {
        return self::where('province', $province)
            ->where('is_active', true)
            ->orderBy('branch_name')
            ->get();
    }
    
    /**
     * Get all branches in a specific district
     * 
     * @param string $district
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByDistrict(string $district)
    {
        return self::where('district', $district)
            ->where('is_active', true)
            ->orderBy('branch_name')
            ->get();
    }
    
    /**
     * Get all active branches grouped by province
     * 
     * @return array
     */
    public static function getAllGroupedByProvince()
    {
        return self::where('is_active', true)
            ->orderBy('province')
            ->orderBy('branch_name')
            ->get()
            ->groupBy('province')
            ->toArray();
    }
}