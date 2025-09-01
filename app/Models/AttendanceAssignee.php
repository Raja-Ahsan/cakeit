<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAssignee extends Model
{
    use HasFactory;

    protected $table = 'attendance_assignee';
    
    protected $fillable = [
        'employee_id',
        'assigned_by',
        'assigned_at',
        'is_active'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the employee who is assigned to manage attendance
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the admin who assigned the attendance manager
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope to get only active assignee
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a user is the current attendance manager
     */
    public static function isAttendanceManager($userId)
    {
        return static::where('employee_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the current attendance manager
     */
    public static function getCurrentManager()
    {
        return static::active()->with('employee')->first();
    }
}
