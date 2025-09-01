<?php

namespace App\Services;

use App\Models\AttendanceAssignee;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceAssigneeService
{
    /**
     * Get current attendance assignee
     */
    public function getCurrentAssignee()
    {
        try {
            $assignee = AttendanceAssignee::active()->with(['employee', 'assignedBy'])->first();
            Log::info('Current assignee query result: ' . json_encode($assignee));
            return $assignee;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Assign a new attendance manager
     */
    public function assignAttendanceManager($employeeId, $assignedBy)
    {
        try {
            DB::beginTransaction();

            // Deactivate current assignee
            AttendanceAssignee::where('is_active', true)->update(['is_active' => false]);

            // Check if this employee was previously assigned
            $existingAssignee = AttendanceAssignee::where('employee_id', $employeeId)->first();
            
            if ($existingAssignee) {
                // Reactivate existing assignment
                $existingAssignee->update([
                    'assigned_by' => $assignedBy,
                    'is_active' => true,
                    'updated_at' => now()
                ]);
                $assignee = $existingAssignee;
            } else {
                // Create new assignee
                $assignee = AttendanceAssignee::create([
                    'employee_id' => $employeeId,
                    'assigned_by' => $assignedBy,
                    'is_active' => true
                ]);
            }

            DB::commit();
            $result = $assignee->load(['employee', 'assignedBy']);
            Log::info('Assignment result: ' . json_encode($result));
            return $result;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Remove current attendance assignee
     */
    public function removeAttendanceManager()
    {
        try {
            return AttendanceAssignee::where('is_active', true)->update(['is_active' => false]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Check if a user is the current attendance manager
     */
    public function isAttendanceManager($userId)
    {
        try {
            return AttendanceAssignee::isAttendanceManager($userId);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get assignee history
     */
    public function getAssigneeHistory()
    {
        try {
            return AttendanceAssignee::with(['employee', 'assignedBy'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
