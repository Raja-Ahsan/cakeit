<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class AttendanceMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find the Users parent menu (id = 12 based on the database structure)
        $usersMenu = Menu::where('name', 'Users')->first();
        
        if ($usersMenu) {
            // Add Employee Attendance menu item under Users
            Menu::firstOrCreate(
                [
                    'name' => 'Employee Attendance',
                    'parent' => $usersMenu->id
                ],
                [
                    'name' => 'Employee Attendance',
                    'language' => 'employee_attendance',
                    'url' => 'attendance',
                    'icon' => 'lab lab-attendance',
                    'priority' => 100,
                    'status' => 1,
                    'parent' => $usersMenu->id,
                    'type' => 1, // BACKEND type
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
