<?php

namespace App\Helpers;

class ColorHelper
{
    private static $dayColors = [
        'Monday' => '#4361ee',
        'Tuesday' => '#3a0ca3', 
        'Wednesday' => '#7209b7',
        'Thursday' => '#4cc9f0',
        'Friday' => '#f72585',
        'Saturday' => '#e63946',
        'Sunday' => '#2a9d8f'
    ];
    
    // Color palette (same colors will always be assigned to same class names)
    private static $colorPalette = [
        '#4361ee', '#3a0ca3', '#7209b7', '#4cc9f0', '#f72585', '#e63946',
        '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#264653'
    ];
    
    public static function getDayColor($day)
    {
        return self::$dayColors[$day] ?? '#6c757d';
    }
    
    public static function getColorFromString($str)
    {
        // Normalize the string
        $str = strtolower(trim($str));
        
        // Create a consistent hash from the string
        $hash = crc32($str);
        
        // Use the hash to pick a color from the palette
        $index = abs($hash) % count(self::$colorPalette);
        $baseColor = self::$colorPalette[$index];
        
        // Add transparency for fade effect (30% opacity)
        return self::addTransparency($baseColor, 0.3);
    }
    
    public static function addTransparency($hexColor, $opacity = 0.3)
    {
        // Remove # if present
        $hex = str_replace('#', '', $hexColor);
        
        // If shorthand (like #fff), expand it
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Return as rgba with opacity
        return "rgba($r, $g, $b, $opacity)";
    }
    
    public static function adjustOpacity($rgbaColor, $newOpacity = 0.3)
    {
        // Extract RGB values from rgba string
        if (preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/', $rgbaColor, $matches)) {
            $r = $matches[1];
            $g = $matches[2];
            $b = $matches[3];
            return "rgba($r, $g, $b, $newOpacity)";
        }
        
        // If it's a hex color, convert to rgba
        if (strpos($rgbaColor, '#') === 0) {
            $hex = str_replace('#', '', $rgbaColor);
            if (strlen($hex) == 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba($r, $g, $b, $newOpacity)";
        }
        
        return $rgbaColor;
    }
    
    public static function getColorPickerColors()
    {
        // Return palette colors for color picker
        return array_map(function($color) {
            return self::addTransparency($color, 0.3);
        }, self::$colorPalette);
    }
    
    // Alternative: HSL method (for more variety)
    public static function getHslColorFromString($str)
    {
        $str = strtolower(trim($str));
        $hash = crc32($str);
        
        // Use hash to generate consistent HSL values
        $hue = abs($hash) % 360; // 0-359
        $saturation = (abs($hash) % 21) + 20; // 20-40%
        $lightness = (abs($hash) % 16) + 70; // 70-85%
        
        return "hsla($hue, {$saturation}%, {$lightness}%, 0.8)";
    }
}