<?php

if (\!function_exists('human_filesize')) {
    /**
     * Convert bytes to human readable format
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    function human_filesize($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
