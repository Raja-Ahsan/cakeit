<?php

namespace Database\Seeders;

use App\Enums\Role as EnumRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AttendancePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First, create the parent permission
        $parentPermission = Permission::firstOrCreate(
            ['name' => 'employee-attendance'],
            [
                'title'      => 'Employee Attendance',
                'name'       => 'employee-attendance',
                'guard_name' => 'sanctum',
                'url'        => 'employee-attendance',
                'parent'     => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create child permissions with parent reference
        $childPermissions = [
            [
                'title'      => 'Attendance Create',
                'name'       => 'attendance_create',
                'guard_name' => 'sanctum',
                'url'        => 'attendance/create',
                'parent'     => $parentPermission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Attendance Edit',
                'name'       => 'attendance_edit',
                'guard_name' => 'sanctum',
                'url'        => 'attendance/edit',
                'parent'     => $parentPermission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Attendance Delete',
                'name'       => 'attendance_delete',
                'guard_name' => 'sanctum',
                'url'        => 'attendance/delete',
                'parent'     => $parentPermission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Attendance Show',
                'name'       => 'attendance_show',
                'guard_name' => 'sanctum',
                'url'        => 'attendance/show',
                'parent'     => $parentPermission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Attendance Export',
                'name'       => 'attendance_export',
                'guard_name' => 'sanctum',
                'url'        => 'attendance/export',
                'parent'     => $parentPermission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert child permissions
        foreach ($childPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Attendance Settings parent permission
        $settingsParentPermission = Permission::firstOrCreate(
            ['name' => 'attendance-settings'],
            [
                'title'      => 'Attendance Settings',
                'name'       => 'attendance-settings',
                'guard_name' => 'sanctum',
                'url'        => 'attendance-settings',
                'parent'     => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create Attendance Settings child permissions
        $settingsChildPermissions = [
            [
                'title'      => 'Assign Attendance Manager',
                'name'       => 'attendance_assign',
                'guard_name' => 'sanctum',
                'url'        => 'attendance-settings/assign',
                'parent'     => $settingsParentPermission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'View Assignment History',
                'name'       => 'attendance_history',
                'guard_name' => 'sanctum',
                'url'        => 'attendance-settings/history',
                'parent'     => $settingsParentPermission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert settings child permissions
        foreach ($settingsChildPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Attendance Manager role if it doesn't exist
        $attendanceManagerRole = Role::firstOrCreate(
            ['name' => 'Attendance Manager'],
            [
                'name' => 'Attendance Manager',
                'guard_name' => 'sanctum',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign permissions to roles
        $adminRole = Role::find(EnumRole::ADMIN);
        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::whereIn('name', [
                'employee-attendance',
                'attendance_create',
                'attendance_edit',
                'attendance_delete',
                'attendance_show',
                'attendance_export',
                'attendance-settings',
                'attendance_assign',
                'attendance_history'
            ])->get());
        }

        $branchManagerRole = Role::find(EnumRole::BRANCH_MANAGER);
        if ($branchManagerRole) {
            $branchManagerRole->givePermissionTo(Permission::whereIn('name', [
                'employee-attendance',
                'attendance_create',
                'attendance_edit',
                'attendance_delete',
                'attendance_show',
                'attendance_export',
                'attendance-settings',
                'attendance_assign',
                'attendance_history'
            ])->get());
        }

        // Assign permissions to Attendance Manager role
        $attendanceManagerRole->givePermissionTo(Permission::whereIn('name', [
            'dashboard',
            'employee-attendance',
            'attendance_create',
            'attendance_edit',
            'attendance_delete',
            'attendance_show',
            'attendance_export',
            'employees',
            'employees_show'
        ])->get());
    }
}
