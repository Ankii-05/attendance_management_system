<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->department = Department::create([
        'name' => 'Human Resources',
        'description' => 'HR department'
    ]);

    $this->adminUser = User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->employeeUser = User::create([
        'name' => 'Standard Employee',
        'email' => 'employee@example.com',
        'password' => Hash::make('password123'),
        'role' => 'employee',
    ]);

    $this->employee = Employee::create([
        'user_id' => $this->employeeUser->id,
        'department_id' => $this->department->id,
        'employee_code' => 'EMP100',
        'name' => 'Standard Employee',
        'email' => 'employee@example.com',
        'mobile_number' => '9876543210',
        'designation' => 'Recruiter',
        'joining_date' => '2024-02-01',
        'status' => 'active',
    ]);
});

test('admin can view employee index page', function () {
    $response = $this->actingAs($this->adminUser)->get('/admin/employees');

    $response->assertStatus(200);
    $response->assertSee('Employee Management');
});

test('admin can view create employee page with auto-generated code', function () {
    $response = $this->actingAs($this->adminUser)->get('/admin/employees/create');

    $response->assertStatus(200);
    $response->assertSee('Auto-generated');
    $response->assertSee('readonly');
});

test('admin can create a new employee and user account', function () {
    $response = $this->actingAs($this->adminUser)->post('/admin/employees', [
        'employee_code' => 'EMP200',
        'name' => 'John New',
        'email' => 'john.new@example.com',
        'mobile_number' => '9123456780',
        'department_id' => $this->department->id,
        'designation' => 'Executive',
        'joining_date' => '2024-03-01',
        'status' => 'active',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('admin.employees.index'));
    $this->assertDatabaseHas('employees', [
        'employee_code' => 'EMP200',
        'email' => 'john.new@example.com',
    ]);
    $this->assertDatabaseHas('users', [
        'email' => 'john.new@example.com',
        'role' => 'employee',
    ]);
});

test('employee creation fails with duplicate email or employee code', function () {
    $response = $this->actingAs($this->adminUser)->post('/admin/employees', [
        'employee_code' => 'EMP100', // Duplicate
        'name' => 'Duplicate Person',
        'email' => 'employee@example.com', // Duplicate
        'mobile_number' => '9123456780',
        'department_id' => $this->department->id,
        'designation' => 'Executive',
        'joining_date' => '2024-03-01',
        'status' => 'active',
    ]);

    $response->assertSessionHasErrors(['employee_code', 'email']);
});

test('employee creation fails when mobile number is not 10 digits or does not start with 6-9', function () {
    // Starts with 5 (invalid)
    $response1 = $this->actingAs($this->adminUser)->post('/admin/employees', [
        'name' => 'Invalid Mobile 1',
        'email' => 'invalid1@example.com',
        'mobile_number' => '5876543210',
        'department_id' => $this->department->id,
        'designation' => 'Executive',
        'joining_date' => '2024-03-01',
        'status' => 'active',
    ]);
    $response1->assertSessionHasErrors(['mobile_number']);

    // Less than 10 digits (9 digits)
    $response2 = $this->actingAs($this->adminUser)->post('/admin/employees', [
        'name' => 'Invalid Mobile 2',
        'email' => 'invalid2@example.com',
        'mobile_number' => '987654321',
        'department_id' => $this->department->id,
        'designation' => 'Executive',
        'joining_date' => '2024-03-01',
        'status' => 'active',
    ]);
    $response2->assertSessionHasErrors(['mobile_number']);

    // More than 10 digits (11 digits)
    $response3 = $this->actingAs($this->adminUser)->post('/admin/employees', [
        'name' => 'Invalid Mobile 3',
        'email' => 'invalid3@example.com',
        'mobile_number' => '98765432100',
        'department_id' => $this->department->id,
        'designation' => 'Executive',
        'joining_date' => '2024-03-01',
        'status' => 'active',
    ]);
    $response3->assertSessionHasErrors(['mobile_number']);
});

test('admin can update employee details', function () {
    $response = $this->actingAs($this->adminUser)->put("/admin/employees/{$this->employee->id}", [
        'employee_code' => 'EMP100',
        'name' => 'Standard Employee Updated',
        'email' => 'employee@example.com',
        'mobile_number' => '9876543299',
        'department_id' => $this->department->id,
        'designation' => 'Lead Recruiter',
        'joining_date' => '2024-02-01',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('admin.employees.index'));
    $this->assertDatabaseHas('employees', [
        'id' => $this->employee->id,
        'name' => 'Standard Employee Updated',
        'designation' => 'Lead Recruiter',
    ]);
});

test('admin can toggle employee status', function () {
    $this->assertEquals('active', $this->employee->status);

    $response = $this->actingAs($this->adminUser)
        ->patch("/admin/employees/{$this->employee->id}/toggle-status");

    $response->assertSessionHas('success');
    $this->assertEquals('inactive', $this->employee->fresh()->status);
});

test('employee user cannot access admin employee routes', function () {
    $response = $this->actingAs($this->employeeUser)->get('/admin/employees');

    $response->assertStatus(403);
});

test('api returns employees list for authenticated users', function () {
    Sanctum::actingAs($this->adminUser);

    $response = $this->getJson('/api/employees');

    $response->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure([
                 'success',
                 'message',
                 'data' => [
                     'data' => [
                         '*' => ['id', 'employee_code', 'name', 'email', 'department']
                     ]
                 ]
             ]);
});

test('api returns single employee details', function () {
    Sanctum::actingAs($this->adminUser);

    $response = $this->getJson("/api/employees/{$this->employee->id}");

    $response->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.employee_code', 'EMP100');
});

test('employee cannot view other employee details via api', function () {
    // Create another employee
    $otherUser = User::create([
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password' => Hash::make('password123'),
        'role' => 'employee',
    ]);

    $otherEmployee = Employee::create([
        'user_id' => $otherUser->id,
        'department_id' => $this->department->id,
        'employee_code' => 'EMP999',
        'name' => 'Other Person',
        'email' => 'other@example.com',
        'mobile_number' => '9999999999',
        'designation' => 'Associate',
        'joining_date' => '2024-01-01',
        'status' => 'active',
    ]);

    // Standard employee tries to access other employee's id
    Sanctum::actingAs($this->employeeUser);

    $response = $this->getJson("/api/employees/{$otherEmployee->id}");

    $response->assertStatus(403)
             ->assertJsonPath('success', false);
});


test('admin can see employee initial password on profile view', function () {
    $this->employee->update(['initial_password' => 'SecretPass123!']);

    $response = $this->actingAs($this->adminUser)->get("/admin/employees/{$this->employee->id}");

    $response->assertStatus(200);
    $response->assertSee('Account Password');
    $response->assertSee('SecretPass123!');
});

test('admin can soft delete an employee and associated user account', function () {
    $employeeId = $this->employee->id;
    $userId = $this->employeeUser->id;

    $response = $this->actingAs($this->adminUser)->delete("/admin/employees/{$employeeId}");

    $response->assertRedirect(route('admin.employees.index'));
    $this->assertSoftDeleted('employees', ['id' => $employeeId]);
    $this->assertSoftDeleted('users', ['id' => $userId]);

    // Model excluded from default query
    $this->assertNull(Employee::find($employeeId));
    // Model found withTrashed
    $this->assertNotNull(Employee::withTrashed()->find($employeeId));
});

test('soft deletes function across all models in database', function () {
    // Department soft delete
    $dept = Department::create(['name' => 'Temp Dept', 'description' => 'Temporary']);
    $dept->delete();
    $this->assertSoftDeleted('departments', ['id' => $dept->id]);
    $this->assertNotNull(Department::withTrashed()->find($dept->id));

    // Attendance soft delete
    $att = \App\Models\Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => '2026-10-23',
        'status' => 'present',
    ]);
    $att->delete();
    $this->assertSoftDeleted('attendance', ['id' => $att->id]);
    $this->assertNotNull(\App\Models\Attendance::withTrashed()->find($att->id));

    // Setting soft delete
    $setting = \App\Models\Setting::create(['key' => 'temp_setting', 'value' => 'abc']);
    $setting->delete();
    $this->assertSoftDeleted('settings', ['id' => $setting->id]);
    $this->assertNotNull(\App\Models\Setting::withTrashed()->find($setting->id));
});
