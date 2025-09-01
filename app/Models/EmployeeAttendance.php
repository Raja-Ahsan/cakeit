<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance';
    
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'status' => 'string'
    ];

    /**
     * Get the employee that owns the attendance record
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the user who created the attendance record
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by employee
     */
    public function scopeEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get today's attendance
     */
    public function scopeToday($query)
    {
        return $query->where('date', Carbon::today());
    }

    /**
     * Get working hours for the day
     */
    public function getWorkingHoursAttribute()
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
            return $checkIn->diffInHours($checkOut, true);
        }
        return 0;
    }

    /**
     * Check if employee is late (check-in after 9:00 AM)
     */
    public function getIsLateAttribute()
    {
        if ($this->check_in) {
            $checkInTime = Carbon::parse($this->check_in);
            $lateThreshold = Carbon::parse('09:00:00');
            return $checkInTime->gt($lateThreshold);
        }
        return false;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'present' => 'badge-success',
            'absent' => 'badge-danger',
            'late' => 'badge-warning',
            'half_day' => 'badge-info',
            'leave' => 'badge-secondary',
            default => 'badge-primary'
        };
    }
}
