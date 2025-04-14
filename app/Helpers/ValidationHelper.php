<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Validates a Zimbabwean ID number
     * Format: 12-345678X-42 (where 12 is province, 345678X is ID number with check letter X, 42 is district)
     * 
     * @param string $idNumber
     * @return bool
     */
    public static function validateZimbabweanID($idNumber)
    {
        // Basic pattern match for Zimbabwean ID
        $pattern = '/^\d{2}-\d{6,7}[A-Z]-\d{2}$/';
        
        if (!preg_match($pattern, $idNumber)) {
            return false;
        }
        
        // Extract province, ID number with check letter, and district
        $parts = explode('-', $idNumber);
        
        // Ensure we have 3 parts
        if (count($parts) !== 3) {
            return false;
        }
        
        // Validate province code (01-63, some provinces aren't used)
        $provinceCode = (int)$parts[0];
        if ($provinceCode < 1 || $provinceCode > 63) {
            return false;
        }
        
        // Validate ID number with check letter
        $idWithCheck = $parts[1];
        $idLength = strlen($idWithCheck);
        
        // ID should be 6 or 7 digits plus 1 letter
        if ($idLength < 7 || $idLength > 8) {
            return false;
        }
        
        // Last character should be a letter
        $checkLetter = substr($idWithCheck, -1);
        if (!ctype_alpha($checkLetter)) {
            return false;
        }
        
        // District code (01-99)
        $districtCode = (int)$parts[2];
        if ($districtCode < 1 || $districtCode > 99) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Format a Zimbabwean ID number with proper capitalization
     * 
     * @param string $idNumber
     * @return string
     */
    public static function formatZimbabweanID($idNumber)
    {
        if (empty($idNumber)) {
            return $idNumber;
        }
        
        // Convert check letter to uppercase
        $parts = explode('-', $idNumber);
        if (count($parts) === 3) {
            $idWithCheck = $parts[1];
            $numericPart = preg_replace('/[^0-9]/', '', $idWithCheck);
            $checkLetter = preg_replace('/[0-9]/', '', $idWithCheck);
            $parts[1] = $numericPart . strtoupper($checkLetter);
            $idNumber = implode('-', $parts);
        }
        
        return $idNumber;
    }
    
    /**
     * Format a Zimbabwean phone number with +263 prefix
     * 
     * @param string $phoneNumber
     * @return string
     */
    public static function formatZimbabweanPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return $phoneNumber;
        }
        
        // Remove spaces, dashes, and any other non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 0, replace with +263
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '+263' . substr($phoneNumber, 1);
        }
        // If it already starts with 263, add + prefix
        else if (substr($phoneNumber, 0, 3) === '263') {
            $phoneNumber = '+' . $phoneNumber;
        }
        // If it doesn't start with +263, add it
        else if (substr($phoneNumber, 0, 4) !== '+263') {
            // If first digit is 7, add +263 prefix
            if (substr($phoneNumber, 0, 1) === '7') {
                $phoneNumber = '+263' . $phoneNumber;
            }
            // Otherwise assume it's a different international format
            else if (substr($phoneNumber, 0, 1) !== '+') {
                $phoneNumber = '+' . $phoneNumber;
            }
        }
        
        return $phoneNumber;
    }
    
    /**
     * Capitalize a person's name (first letter of each word, respecting hyphenated names)
     * 
     * @param string $name
     * @return string
     */
    public static function capitalizeName($name)
    {
        if (empty($name)) {
            return $name;
        }
        
        // Split on spaces first
        $parts = explode(' ', $name);
        
        foreach ($parts as &$part) {
            // Handle hyphenated names
            if (strpos($part, '-') !== false) {
                $hyphenParts = explode('-', $part);
                foreach ($hyphenParts as &$hyphenPart) {
                    $hyphenPart = ucfirst(strtolower($hyphenPart));
                }
                $part = implode('-', $hyphenParts);
            } else {
                $part = ucfirst(strtolower($part));
            }
        }
        
        return implode(' ', $parts);
    }
    
    /**
     * Validate SSB station code (4 digits)
     * 
     * @param string $stationCode
     * @return bool
     */
    public static function validateStationCode($stationCode)
    {
        return preg_match('/^\d{4}$/', $stationCode) === 1;
    }
    
    /**
     * Validate SSB department code (4 digits)
     * 
     * @param string $departmentCode
     * @return bool
     */
    public static function validateDepartmentCode($departmentCode)
    {
        return preg_match('/^\d{4}$/', $departmentCode) === 1;
    }
}