<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceAssigneeService;
use App\Services\EmployeeService;
use Exception;
use Illuminate\Http\Request;

class AttendanceAssigneeController extends Controller
{
    private AttendanceAssigneeService $assigneeService;
    private EmployeeService $employeeService;

    public function __construct(AttendanceAssigneeService $assigneeService, EmployeeService $employeeService)
    {
        $this->assigneeService = $assigneeService;
        $this->employeeService = $employeeService;
        // Temporarily remove middleware to test the API
        // $this->middleware(['permission:settings'])->only('index', 'store', 'destroy', 'debug');
    }

    /**
     * Get current attendance assignee
     */
    public function index()
    {
        try {
            $currentAssignee = $this->assigneeService->getCurrentAssignee();
            
            // Get employees using a simpler approach
            $employees = \App\Models\User::with('roles')
                ->whereHas('roles', function($query) {
                    $query->where('name', '!=', 'Admin')
                          ->where('name', '!=', 'Customer')
                          ->where('name', '!=', 'Waiter')
                          ->where('name', '!=', 'Chef');
                })
                ->get(['id', 'name', 'email']);
            
            $assigneeHistory = $this->assigneeService->getAssigneeHistory();

            return response([
                'status' => true,
                'data' => [
                    'current_assignee' => $currentAssignee,
                    'employees' => $employees,
                    'assignee_history' => $assigneeHistory
                ]
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Assign a new attendance manager
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:users,id'
            ]);

            $assignee = $this->assigneeService->assignAttendanceManager(
                $request->employee_id,
                auth()->id()
            );

            return response([
                'status' => true,
                'message' => 'Attendance manager assigned successfully',
                'data' => $assignee
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove current attendance assignee
     */
    public function destroy()
    {
        try {
            $this->assigneeService->removeAttendanceManager();

            return response([
                'status' => true,
                'message' => 'Attendance manager removed successfully'
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Debug method to check database state
     */
    public function debug()
    {
        try {
            $allAssignees = \App\Models\AttendanceAssignee::with(['employee', 'assignedBy'])->get();
            $activeAssignees = \App\Models\AttendanceAssignee::active()->with(['employee', 'assignedBy'])->get();
            $totalCount = \App\Models\AttendanceAssignee::count();
            
            return response([
                'status' => true,
                'data' => [
                    'total_assignees' => $totalCount,
                    'all_assignees' => $allAssignees,
                    'active_assignees' => $activeAssignees,
                    'current_assignee' => $this->assigneeService->getCurrentAssignee()
                ]
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Simple test method
     */
    public function test()
    {
        return response([
            'status' => true,
            'message' => 'API is working',
            'data' => [
                'timestamp' => now(),
                'user' => auth()->user() ? auth()->user()->name : 'Not authenticated'
            ]
        ]);
    }
}
