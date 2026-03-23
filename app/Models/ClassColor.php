<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\ColorHelper;

class ClassColor extends Model
{
    protected $fillable = ['class_name', 'color'];
    
    /**
     * Get or create a color for a class name
     */
    public static function getColorForClass(string $className): string
    {
        // Normalize the class name
        $normalizedName = strtolower(trim($className));
        
        // Try to find existing color
        $colorRecord = self::where('class_name', $normalizedName)->first();
        
        if ($colorRecord) {
            return $colorRecord->color;
        }
        
        // Generate new faded color
        $newColor = ColorHelper::getColorFromString($normalizedName);
        
        // Create and save new record
        $colorRecord = self::create([
            'class_name' => $normalizedName,
            'color' => $newColor
        ]);
        
        return $newColor;
    }
    
    /**
     * Update color for a class name
     */
    public static function updateColorForClass(string $className, string $color): bool
    {
        $normalizedName = strtolower(trim($className));
        
        return (bool) self::updateOrCreate(
            ['class_name' => $normalizedName],
            ['color' => $color]
        );
    }
    
    /**
     * Get color by class name
     */
    public static function getColorByName(string $className): ?string
    {
        $normalizedName = strtolower(trim($className));
        $colorRecord = self::where('class_name', $normalizedName)->first();
        
        return $colorRecord ? $colorRecord->color : null;
    }
    
    /**
     * Update colors for all classes with this name
     */
    public static function updateAllClassesWithColor(string $oldClassName, string $newClassName, string $newColor = null): void
    {
        $normalizedOldName = strtolower(trim($oldClassName));
        $normalizedNewName = strtolower(trim($newClassName));
        
        // If names are different, we need to update or create new color record
        if ($normalizedOldName !== $normalizedNewName) {
            // Remove old color record if no other classes use it
            $oldColorRecord = self::where('class_name', $normalizedOldName)->first();
            if ($oldColorRecord) {
                // Check if any classes still use the old name
                $classCount = \App\Models\ClassSchedule::whereRaw('LOWER(name) = ?', [$normalizedOldName])->count();
                if ($classCount === 0) {
                    $oldColorRecord->delete();
                }
            }
            
            // Create or update new color record
            if ($newColor) {
                self::updateOrCreate(
                    ['class_name' => $normalizedNewName],
                    ['color' => $newColor]
                );
            }
        } else {
            // Same name, just update color if provided
            if ($newColor) {
                self::updateOrCreate(
                    ['class_name' => $normalizedNewName],
                    ['color' => $newColor]
                );
            }
        }
    }
    
    /**
     * Delete color for a class name
     */
    public static function deleteColorForClass(string $className): void
    {
        $normalizedName = strtolower(trim($className));
        
        // Only delete if no other classes are using this color
        $colorRecord = self::where('class_name', $normalizedName)->first();
        
        if ($colorRecord) {
            // Check if any other classes are using this same class name
            $classCount = \App\Models\ClassSchedule::whereRaw('LOWER(name) = ?', [$normalizedName])->count();
            if ($classCount === 0) {
                $colorRecord->delete();
            }
        }
    }
    
    /**
     * Get all colors for bulk operations
     */
    public static function getAllColors(): array
    {
        return self::pluck('color', 'class_name')->toArray();
    }
}