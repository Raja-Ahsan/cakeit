<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'email' => $this->employee->email,
                'phone' => $this->employee->phone,
                'username' => $this->employee->username,
                'branch_id' => $this->employee->branch_id,
                'status' => $this->employee->status,
                'role' => optional($this->employee->roles->first())->name ?? 'No Role'
            ],
            'date' => $this->date->format('Y-m-d'),
            'check_in' => $this->check_in ? \Carbon\Carbon::parse($this->check_in)->format('H:i:s') : null,
            'check_out' => $this->check_out ? \Carbon\Carbon::parse($this->check_out)->format('H:i:s') : null,
            'status' => $this->status,
            'status_badge_class' => $this->status_badge_class,
            'notes' => $this->notes,
            'working_hours' => $this->working_hours,
            'is_late' => $this->is_late,
            'created_by' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
