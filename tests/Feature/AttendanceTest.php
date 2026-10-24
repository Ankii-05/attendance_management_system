<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->department = Department::create([
        'name' => 'Operations',
        'description' => 'Operations dept'
    ]);

    $this->adminUser = User::create([
        'name' => 'Admin Boss',
        'email' => 'admin.ops@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->employeeUser = User::create([
        'name' => 'Field Worker',
        'email' => 'field.worker@example.com',
        'password' => Hash::make('password123'),
        'role' => 'employee',
    ]);

    $this->employee = Employee::create([
        'user_id' => $this->employeeUser->id,
        'department_id' => $this->department->id,
        'employee_code' => 'OPS01',
        'name' => 'Field Worker',
        'email' => 'field.worker@example.com',
        'mobile_number' => '9876543210',
        'designation' => 'Field Officer',
        'joining_date' => '2024-01-01',
        'status' => 'active',
    ]);
});

test('employee can check-in via web', function () {
    $response = $this->actingAs($this->employeeUser)
        ->post('/employee/attendance/check-in', [
            'notes' => 'On-site check-in',
        ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('attendance', [
        'employee_id' => $this->employee->id,
        'status' => 'present',
    ]);
});

test('employee cannot double check-in on the same day', function () {
    $today = Carbon::today()->toDateString();
    Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => $today,
        'check_in' => Carbon::now()->subHours(2),
        'status' => 'present',
    ]);

    $response = $this->actingAs($this->employeeUser)
        ->post('/employee/attendance/check-in');

    $response->assertSessionHasErrors('general');
});

test('employee checking out with less than 4 hours is marked absent', function () {
    $today = Carbon::today()->toDateString();
    $testNow = Carbon::today()->setTime(12, 30, 0); // 2.5 hours after 10 AM
    Carbon::setTestNow($testNow);

    $checkInTime = Carbon::today()->setTime(10, 0, 0); // 10:00 AM

    Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => $today,
        'check_in' => $checkInTime,
        'status' => 'present',
    ]);

    $response = $this->actingAs($this->employeeUser)
        ->post('/employee/attendance/check-out', [
            'notes' => 'Leaving very early',
        ]);

    $response->assertSessionHas('success');

    $attendance = Attendance::where('employee_id', $this->employee->id)->whereDate('date', $today)->first();
    $this->assertNotNull($attendance->check_out);
    $this->assertEquals('absent', $attendance->status);
    $this->assertEquals(2.5, $attendance->total_hours);

    Carbon::setTestNow();
});

test('employee checking out between 4 and 8 hours is marked half_day', function () {
    $today = Carbon::today()->toDateString();
    $testNow = Carbon::today()->setTime(15, 0, 0); // 5 hours after 10 AM
    Carbon::setTestNow($testNow);

    $checkInTime = Carbon::today()->setTime(10, 0, 0); // 10:00 AM

    Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => $today,
        'check_in' => $checkInTime,
        'status' => 'present',
    ]);

    $response = $this->actingAs($this->employeeUser)
        ->post('/employee/attendance/check-out', [
            'notes' => 'Leaving for half day',
        ]);

    $response->assertSessionHas('success');

    $attendance = Attendance::where('employee_id', $this->employee->id)->whereDate('date', $today)->first();
    $this->assertNotNull($attendance->check_out);
    $this->assertEquals('half_day', $attendance->status);
    $this->assertEquals(5.0, $attendance->total_hours);

    Carbon::setTestNow();
});

test('employee checking out after 8 hours is marked present', function () {
    $today = Carbon::today()->toDateString();
    $testNow = Carbon::today()->setTime(18, 30, 0); // 8.5 hours after 10 AM
    Carbon::setTestNow($testNow);

    $checkInTime = Carbon::today()->setTime(10, 0, 0); // 10:00 AM

    Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => $today,
        'check_in' => $checkInTime,
        'status' => 'present',
    ]);

    $response = $this->actingAs($this->employeeUser)
        ->post('/employee/attendance/check-out', [
            'notes' => 'Completed day shift',
        ]);

    $response->assertSessionHas('success');

    $attendance = Attendance::where('employee_id', $this->employee->id)->whereDate('date', $today)->first();
    $this->assertNotNull($attendance->check_out);
    $this->assertEquals('present', $attendance->status);
    $this->assertEquals(8.5, $attendance->total_hours);

    Carbon::setTestNow();
});

test('api check-in works successfully for authenticated employee', function () {
    Sanctum::actingAs($this->employeeUser);

    $response = $this->postJson('/api/attendance/check-in', [
        'notes' => 'Mobile check in',
    ]);

    $response->assertStatus(201)
             ->assertJsonPath('success', true)
             ->assertJsonPath('message', 'Checked in successfully.');
});

test('api check-out works successfully for authenticated employee', function () {
    $today = Carbon::today()->toDateString();
    $testNow = Carbon::today()->setTime(19, 30, 0); // 7:30 PM
    Carbon::setTestNow($testNow);

    Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => $today,
        'check_in' => Carbon::today()->setTime(10, 0, 0),
        'status' => 'present',
    ]);

    Sanctum::actingAs($this->employeeUser);

    $response = $this->postJson('/api/attendance/check-out', [
        'notes' => 'Shift ended',
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.status', 'present');

    Carbon::setTestNow();
});

test('api attendance retrieval returns records', function () {
    $today = Carbon::today()->toDateString();
    Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => $today,
        'check_in' => Carbon::now()->subHours(4),
        'check_out' => Carbon::now(),
        'total_hours' => 4.00,
        'status' => 'present',
    ]);

    Sanctum::actingAs($this->employeeUser);

    $response = $this->getJson('/api/attendance');

    $response->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure([
                 'success',
                 'message',
                 'data' => [
                     'data' => [
                         '*' => ['id', 'date', 'check_in', 'check_out', 'total_hours', 'status']
                     ]
                 ]
             ]);
});

test('admin can view today absent employees list', function () {
    // The employee has not checked in today, so should appear in absent list
    $response = $this->actingAs($this->adminUser)->get('/admin/attendance/absent');

    $response->assertStatus(200);
    $response->assertSee('Field Worker');
    $response->assertSee('Today\'s Absent Employees');
});
