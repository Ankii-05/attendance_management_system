<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->department = Department::create([
        'name' => 'Engineering',
        'description' => 'Software engineering department'
    ]);
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Sign In');
});

test('admin can log in and is redirected to admin dashboard', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin.test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->post('/login', [
        'email' => 'admin.test@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('admin.dashboard'));
});

test('employee can log in and is redirected to employee dashboard', function () {
    $user = User::create([
        'name' => 'Test Employee',
        'email' => 'emp.test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'employee',
    ]);

    Employee::create([
        'user_id' => $user->id,
        'department_id' => $this->department->id,
        'employee_code' => 'EMPTEST01',
        'name' => 'Test Employee',
        'email' => 'emp.test@example.com',
        'mobile_number' => '9876543210',
        'designation' => 'Developer',
        'joining_date' => '2024-01-01',
        'status' => 'active',
    ]);

    $response = $this->post('/login', [
        'email' => 'emp.test@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('employee.dashboard'));
});

test('inactive employee cannot log in', function () {
    $user = User::create([
        'name' => 'Inactive Employee',
        'email' => 'inactive@example.com',
        'password' => Hash::make('password123'),
        'role' => 'employee',
    ]);

    Employee::create([
        'user_id' => $user->id,
        'department_id' => $this->department->id,
        'employee_code' => 'EMPTEST02',
        'name' => 'Inactive Employee',
        'email' => 'inactive@example.com',
        'mobile_number' => '9876543211',
        'designation' => 'Developer',
        'joining_date' => '2024-01-01',
        'status' => 'inactive',
    ]);

    $response = $this->post('/login', [
        'email' => 'inactive@example.com',
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('user cannot log in with invalid password', function () {
    User::create([
        'name' => 'Valid User',
        'email' => 'valid@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->post('/login', [
        'email' => 'valid@example.com',
        'password' => 'wrongpassword',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('authenticated user can log out', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin.logout@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('api login returns token on valid credentials', function () {
    User::create([
        'name' => 'API Admin',
        'email' => 'api.admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'api.admin@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonStructure([
                 'success',
                 'message',
                 'data' => [
                     'token',
                     'token_type',
                     'user' => ['id', 'name', 'email', 'role'],
                 ]
             ]);
});

test('api login fails with 401 on invalid credentials', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(401)
             ->assertJsonPath('success', false)
             ->assertJsonPath('message', 'Invalid email or password.');
});
