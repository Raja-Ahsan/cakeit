<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\AttendanceAssignee;
use App\Models\User;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\AttendanceRequest;
use App\Enums\AttendanceStatus;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeAttendanceService
{
    protected array $attendanceFilter = [
        'employee_id',
        'date',
        'status',
        'start_date',
        'end_date'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'date';
            $orderType   = $request->get('order_type') ?? 'desc';

            $query = EmployeeAttendance::with(['employee', 'createdBy']);

            // Apply filters
            foreach ($requests as $key => $value) {
                if (in_array($key, $this->attendanceFilter) && !empty($value)) {
                    switch ($key) {
                        case 'employee_id':
                            $query->where('employee_id', $value);
                            break;
                        case 'date':
                            $query->where('date', $value);
                            break;
                        case 'status':
                            $query->where('status', $value);
                            break;
                        case 'start_date':
                            $query->where('date', '>=', $value);
                            break;
                        case 'end_date':
                            $query->where('date', '<=', $value);
                            break;
                    }
                }
            }

            return $query->orderBy($orderColumn, $orderType)->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(AttendanceRequest $request)
    {
        try {
            DB::beginTransaction();

            // Check if attendance already exists for this employee on this date
            $existingAttendance = EmployeeAttendance::where('employee_id', $request->employee_id)
                ->where('date', $request->date)
                ->first();

            if ($existingAttendance) {
                throw new Exception('Attendance record already exists for this employee on this date');
            }

            // Create new attendance record
            $attendance = EmployeeAttendance::create([
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'status' => $request->status,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);

            DB::commit();
            return $attendance->load(['employee', 'createdBy']);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(AttendanceRequest $request, EmployeeAttendance $attendance)
    {
        try {
            DB::beginTransaction();

            // Check if updating to a date that already has another record for this employee
            if ($request->date != $attendance->date) {
                $existingAttendance = EmployeeAttendance::where('employee_id', $request->employee_id)
                    ->where('date', $request->date)
                    ->where('id', '!=', $attendance->id)
                    ->first();

                if ($existingAttendance) {
                    throw new Exception('Attendance record already exists for this employee on the new date');
                }
            }

            $attendance->update([
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'status' => $request->status,
                'notes' => $request->notes
            ]);

            DB::commit();
            return $attendance->load(['employee', 'createdBy']);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(EmployeeAttendance $attendance): void
    {
        try {
            $attendance->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(EmployeeAttendance $attendance): EmployeeAttendance
    {
        try {
            return $attendance->load(['employee', 'createdBy']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get today's attendance for all employees
     */
    public function getTodayAttendance()
    {
        try {
            $today = Carbon::today();
            
            // Get all active employees
            $employees = User::whereHas('roles', function ($query) {
                $query->whereNotIn('id', [1, 2]); // Exclude Admin and Customer roles
            })->where('status', 5)->get(); // 5 = Active status

            $attendanceData = [];

            foreach ($employees as $employee) {
                $attendance = EmployeeAttendance::where('employee_id', $employee->id)
                    ->where('date', $today)
                    ->first();

                $attendanceData[] = [
                    'employee' => $employee,
                    'attendance' => $attendance,
                    'has_attendance' => $attendance !== null
                ];
            }

            return $attendanceData;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Mark check-in for an employee
     */
    public function markCheckIn($employeeId, $date = null)
    {
        try {
            $date = $date ?: Carbon::today();
            
            $attendance = EmployeeAttendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                [
                    'check_in' => Carbon::now()->format('H:i:s'),
                    'status' => AttendanceStatus::PRESENT,
                    'created_by' => auth()->id()
                ]
            );

            return $attendance->load(['employee', 'createdBy']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Mark check-out for an employee
     */
    public function markCheckOut($employeeId, $date = null)
    {
        try {
            $date = $date ?: Carbon::today();
            
            $attendance = EmployeeAttendance::where('employee_id', $employeeId)
                ->where('date', $date)
                ->first();

            if (!$attendance) {
                throw new Exception('No check-in record found for this employee on this date');
            }

            if ($attendance->check_out) {
                throw new Exception('Employee has already checked out for this date');
            }

            $attendance->update([
                'check_out' => Carbon::now()->format('H:i:s')
            ]);

            return $attendance->load(['employee', 'createdBy']);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get employee's own attendance history
     */
    public function getEmployeeAttendance($employeeId, PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'date';
            $orderType   = $request->get('order_type') ?? 'desc';

            $query = EmployeeAttendance::with(['employee', 'createdBy'])
                ->where('employee_id', $employeeId);

            // Apply date filters
            if (!empty($requests['start_date'])) {
                $query->where('date', '>=', $requests['start_date']);
            }
            if (!empty($requests['end_date'])) {
                $query->where('date', '<=', $requests['end_date']);
            }
            if (!empty($requests['status'])) {
                $query->where('status', $requests['status']);
            }

            return $query->orderBy($orderColumn, $orderType)->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
