<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\EmployeeAttendanceResource;
use App\Services\EmployeeAttendanceService;
use App\Services\AttendanceAssigneeService;
use Exception;

class EmployeeAttendanceController extends Controller
{
    private EmployeeAttendanceService $attendanceService;
    private AttendanceAssigneeService $assigneeService;

    public function __construct(EmployeeAttendanceService $attendanceService, AttendanceAssigneeService $assigneeService)
    {
        $this->attendanceService = $attendanceService;
        $this->assigneeService = $assigneeService;
        $this->middleware(['permission:attendance'])->only('index', 'show', 'todayAttendance');
        $this->middleware(['permission:attendance_create'])->only('store');
        $this->middleware(['permission:attendance_edit'])->only('update');
        $this->middleware(['permission:attendance_delete'])->only('destroy');
        $this->middleware(['permission:attendance_edit'])->only('markCheckIn', 'markCheckOut');
    }

    /**
     * Display a listing of attendance records
     */
    public function index(PaginateRequest $request)
    {
        try {
            return EmployeeAttendanceResource::collection($this->attendanceService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Store a newly created attendance record
     */
    public function store(AttendanceRequest $request)
    {
        try {
            // Check if current user is attendance manager or admin
            if (!$this->assigneeService->isAttendanceManager(auth()->id()) && !auth()->user()->hasRole('Admin')) {
                return response(['status' => false, 'message' => 'Only assigned attendance manager or admin can create attendance records'], 403);
            }

            return new EmployeeAttendanceResource($this->attendanceService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified attendance record
     */
    public function show($id)
    {
        try {
            // Find the attendance record first
            $attendance = \App\Models\EmployeeAttendance::findOrFail($id);
            $attendance = $this->attendanceService->show($attendance);
            return new EmployeeAttendanceResource($attendance);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update the specified attendance record
     */
    public function update(AttendanceRequest $request, $id)
    {
        try {
            // Check if current user is attendance manager or admin
            if (!$this->assigneeService->isAttendanceManager(auth()->id()) && !auth()->user()->hasRole('Admin')) {
                return response(['status' => false, 'message' => 'Only assigned attendance manager or admin can update attendance records'], 403);
            }

            // Find the attendance record first
            $attendance = \App\Models\EmployeeAttendance::findOrFail($id);
            $attendance = $this->attendanceService->update($request, $attendance);
            return new EmployeeAttendanceResource($attendance);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy($id)
    {
        try {
            // Check if current user is attendance manager or admin
            if (!$this->assigneeService->isAttendanceManager(auth()->id()) && !auth()->user()->hasRole('Admin')) {
                return response(['status' => false, 'message' => 'Only assigned attendance manager or admin can delete attendance records'], 403);
            }

            // Find the attendance record first
            $attendance = \App\Models\EmployeeAttendance::findOrFail($id);
            $this->attendanceService->destroy($attendance);
            return response(['status' => true, 'message' => 'Attendance record deleted successfully']);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get today's attendance for all employees
     */
    public function todayAttendance()
    {
        try {
            $attendance = $this->attendanceService->getTodayAttendance();
            return response(['status' => true, 'data' => $attendance]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Mark check-in for an employee
     */
    public function markCheckIn($employeeId)
    {
        try {
            // Check if current user is attendance manager or admin
            if (!$this->assigneeService->isAttendanceManager(auth()->id()) && !auth()->user()->hasRole('Admin')) {
                return response(['status' => false, 'message' => 'Only assigned attendance manager or admin can mark attendance'], 403);
            }

            $attendance = $this->attendanceService->markCheckIn($employeeId);
            return new EmployeeAttendanceResource($attendance);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Mark check-out for an employee
     */
    public function markCheckOut($employeeId)
    {
        try {
            // Check if current user is attendance manager or admin
            if (!$this->assigneeService->isAttendanceManager(auth()->id()) && !auth()->user()->hasRole('Admin')) {
                return response(['status' => false, 'message' => 'Only assigned attendance manager or admin can mark attendance'], 403);
            }

            $attendance = $this->attendanceService->markCheckOut($employeeId);
            return new EmployeeAttendanceResource($attendance);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
