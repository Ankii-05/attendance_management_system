<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with initial demo data.
     */
    public function run(): void
    {
        // 1. Create System Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Create Departments
        $departmentsData = [
            [
                'name' => 'Information Technology',
                'description' => 'Software engineering, DevOps, and technical support.',
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Talent acquisition, employee welfare, and company policies.',
            ],
            [
                'name' => 'Finance & Accounts',
                'description' => 'Financial planning, accounting, budgeting, and taxation.',
            ],
            [
                'name' => 'Marketing & Sales',
                'description' => 'Digital advertising, client acquisition, and public relations.',
            ],
            [
                'name' => 'Operations',
                'description' => 'Process management, logistics, and office facilities.',
            ],
        ];

        $deptMap = [];
        foreach ($departmentsData as $dept) {
            $created = Department::firstOrCreate(
                ['name' => $dept['name']],
                ['description' => $dept['description']]
            );
            $deptMap[$dept['name']] = $created->id;
        }

        // 3. Create Sample Employees with User Accounts
        $employeesData = [
            [
                'code' => 'EMP001',
                'name' => 'Rahul Sharma',
                'email' => 'rahul.sharma@example.com',
                'mobile' => '9876543210',
                'dept' => 'Information Technology',
                'designation' => 'Senior Software Engineer',
                'joining_date' => '2024-01-15',
                'status' => 'active',
            ],
            [
                'code' => 'EMP002',
                'name' => 'Priya Patel',
                'email' => 'priya.patel@example.com',
                'mobile' => '9876543211',
                'dept' => 'Human Resources',
                'designation' => 'HR Specialist',
                'joining_date' => '2024-02-01',
                'status' => 'active',
            ],
            [
                'code' => 'EMP003',
                'name' => 'Amit Verma',
                'email' => 'amit.verma@example.com',
                'mobile' => '9876543212',
                'dept' => 'Finance & Accounts',
                'designation' => 'Senior Accountant',
                'joining_date' => '2024-03-10',
                'status' => 'active',
            ],
            [
                'code' => 'EMP004',
                'name' => 'Neha Gupta',
                'email' => 'neha.gupta@example.com',
                'mobile' => '9876543213',
                'dept' => 'Marketing & Sales',
                'designation' => 'Marketing Executive',
                'joining_date' => '2024-04-12',
                'status' => 'active',
            ],
            [
                'code' => 'EMP005',
                'name' => 'Vikas Singh',
                'email' => 'vikas.singh@example.com',
                'mobile' => '9876543214',
                'dept' => 'Operations',
                'designation' => 'Operations Coordinator',
                'joining_date' => '2024-05-20',
                'status' => 'active',
            ],
            [
                'code' => 'EMP006',
                'name' => 'Anjali Mehta',
                'email' => 'anjali.mehta@example.com',
                'mobile' => '9876543215',
                'dept' => 'Information Technology',
                'designation' => 'Junior Developer',
                'joining_date' => '2024-06-01',
                'status' => 'inactive',
            ],
        ];

        $createdEmployees = [];
        foreach ($employeesData as $empData) {
            // Create user account for employee
            $user = User::firstOrCreate(
                ['email' => $empData['email']],
                [
                    'name' => $empData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'employee',
                ]
            );

            // Create employee record
            $employee = Employee::firstOrCreate(
                ['employee_code' => $empData['code']],
                [
                    'user_id' => $user->id,
                    'department_id' => $deptMap[$empData['dept']],
                    'name' => $empData['name'],
                    'email' => $empData['email'],
                    'mobile_number' => $empData['mobile'],
                    'designation' => $empData['designation'],
                    'joining_date' => $empData['joining_date'],
                    'status' => $empData['status'],
                    'initial_password' => 'password123',
                ]
            );

            $createdEmployees[] = $employee;
        }

        // Seed default office timing settings
        \App\Models\Setting::firstOrCreate(['key' => 'office_start_time'], ['value' => '10:00']);
        \App\Models\Setting::firstOrCreate(['key' => 'office_end_time'], ['value' => '19:00']);

        // 4. Create Historical Attendance (Past 3 days)
        $today = Carbon::today();
        for ($i = 3; $i >= 1; $i--) {
            $pastDate = $today->copy()->subDays($i);
            
            // Skip weekends
            if ($pastDate->isWeekend()) {
                continue;
            }

            foreach ($createdEmployees as $index => $emp) {
                if ($emp->status !== 'active') {
                    continue;
                }

                // Make some employees absent in historical data
                if ($index === 3 && $i === 2) {
                    Attendance::firstOrCreate(
                        ['employee_id' => $emp->id, 'date' => $pastDate->toDateString()],
                        [
                            'check_in' => null,
                            'check_out' => null,
                            'total_hours' => 0.00,
                            'status' => 'absent',
                            'notes' => 'On sick leave',
                        ]
                    );
                    continue;
                }

                $inTime = $pastDate->copy()->setTime(9, rand(5, 30), 0);
                $outTime = $pastDate->copy()->setTime(17, rand(30, 59), 0);
                $hours = round($inTime->diffInMinutes($outTime) / 60, 2);

                Attendance::firstOrCreate(
                    ['employee_id' => $emp->id, 'date' => $pastDate->toDateString()],
                    [
                        'check_in' => $inTime,
                        'check_out' => $outTime,
                        'total_hours' => $hours,
                        'status' => 'present',
                        'notes' => 'Regular working hours',
                    ]
                );
            }
        }

        // 5. Create Today's Attendance (Some checked in, some checked out, some absent)
        $todayStr = $today->toDateString();

        // EMP001 (Rahul) - Checked in and Checked out
        if (isset($createdEmployees[0])) {
            $inTime = $today->copy()->setTime(9, 10, 0);
            $outTime = $today->copy()->setTime(17, 45, 0);
            $hours = round($inTime->diffInMinutes($outTime) / 60, 2);

            Attendance::firstOrCreate(
                ['employee_id' => $createdEmployees[0]->id, 'date' => $todayStr],
                [
                    'check_in' => $inTime,
                    'check_out' => $outTime,
                    'total_hours' => $hours,
                    'status' => 'present',
                    'notes' => 'Checked out for the day',
                ]
            );
        }

        // EMP002 (Priya) - Checked in only (currently working)
        if (isset($createdEmployees[1])) {
            $inTime = $today->copy()->setTime(9, 25, 0);

            Attendance::firstOrCreate(
                ['employee_id' => $createdEmployees[1]->id, 'date' => $todayStr],
                [
                    'check_in' => $inTime,
                    'check_out' => null,
                    'total_hours' => 0.00,
                    'status' => 'present',
                    'notes' => 'Currently active in office',
                ]
            );
        }

        // EMP003 (Amit) - Checked in only (currently working)
        if (isset($createdEmployees[2])) {
            $inTime = $today->copy()->setTime(9, 40, 0);

            Attendance::firstOrCreate(
                ['employee_id' => $createdEmployees[2]->id, 'date' => $todayStr],
                [
                    'check_in' => $inTime,
                    'check_out' => null,
                    'total_hours' => 0.00,
                    'status' => 'present',
                    'notes' => 'Checked in from web portal',
                ]
            );
        }

        // EMP004 (Neha) and EMP005 (Vikas) have NOT checked in today -> will be counted as Absent for today!
    }
}
