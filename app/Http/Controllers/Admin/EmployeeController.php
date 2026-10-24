<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    // Display employee listing or return Yajra DataTable JSON
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Employee::with('department')->select('employees.*');

            // Department filter
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::eloquent($query)
                ->addColumn('department_name', function ($employee) {
                    return $employee->department ? e($employee->department->name) : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('joining_date_formatted', function ($employee) {
                    return $employee->joining_date ? $employee->joining_date->format('d M, Y') : 'N/A';
                })
                ->addColumn('status_badge', function ($employee) {
                    if ($employee->status === 'active') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Active</span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times-circle me-1"></i>Inactive</span>';
                })
                ->addColumn('password_display', function ($employee) {
                    $pwd = e($employee->initial_password ?? 'Password@123');
                    return '
                        <div class="d-inline-flex align-items-center">
                            <span class="font-monospace small tbl-password-val" data-pwd="' . $pwd . '">••••••••</span>
                            <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1 toggle-tbl-pwd" title="Show/Hide password">
                                <i class="far fa-eye" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                    ';
                })
                ->addColumn('actions', function ($employee) {
                    $viewUrl = route('admin.employees.show', $employee->id);
                    $editUrl = route('admin.employees.edit', $employee->id);
                    $toggleUrl = route('admin.employees.toggle-status', $employee->id);
                    $deleteUrl = route('admin.employees.destroy', $employee->id);
                    $toggleText = $employee->status === 'active' ? 'Deactivate' : 'Activate';
                    $toggleIcon = $employee->status === 'active' ? 'user-slash' : 'user-check';
                    $toggleBtnClass = $employee->status === 'active' ? 'btn-toggle-deactivate' : 'btn-toggle-activate';
                    $actionColor = $employee->status === 'active' ? '#d97706' : '#16a34a';

                    return '
                        <div class="table-actions">
                            <a href="' . $viewUrl . '" class="table-action-btn btn-view" title="View Profile" data-bs-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="' . $editUrl . '" class="table-action-btn btn-edit" title="Edit Employee" data-bs-toggle="tooltip">
                                <i class="fas fa-pen-to-square"></i>
                            </a>
                            <form action="' . $toggleUrl . '" method="POST" class="d-inline-flex m-0 p-0" data-confirm="Are you sure you want to ' . strtolower($toggleText) . ' ' . e($employee->name) . '?" data-confirm-btn="Yes, ' . $toggleText . '" data-confirm-color="' . $actionColor . '">
                                ' . csrf_field() . '
                                ' . method_field('PATCH') . '
                                <button type="submit" class="table-action-btn ' . $toggleBtnClass . '" title="' . $toggleText . ' Employee" data-bs-toggle="tooltip">
                                    <i class="fas fa-' . $toggleIcon . '"></i>
                                </button>
                            </form>
                            <form action="' . $deleteUrl . '" method="POST" class="d-inline-flex m-0 p-0" data-confirm="Are you sure you want to soft delete ' . e($employee->name) . '?" data-confirm-btn="Yes, Delete" data-confirm-color="#dc2626" data-confirm-icon="warning">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="table-action-btn btn-delete" title="Delete Employee" data-bs-toggle="tooltip">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['department_name', 'status_badge', 'password_display', 'actions'])
                ->make(true);
        }

        $departments = Department::orderBy('name')->get();

        return view('admin.employees.index', compact('departments'));
    }

    // Show form to add new employee
    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        $generatedCode = $this->generateNextEmployeeCode();

        return view('admin.employees.create', compact('departments', 'generatedCode'));
    }

    // Store newly created employee and associated user account
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Use submitted employee code or auto-generate
            $employeeCode = $request->filled('employee_code') ? $request->employee_code : $this->generateNextEmployeeCode();

            // Create user account for login
            $userPassword = $request->filled('password') ? $request->password : 'Password@123';
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($userPassword),
                'role' => 'employee',
            ]);

            // Create employee profile
            Employee::create([
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'employee_code' => $employeeCode,
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'status' => $request->status,
                'initial_password' => $userPassword,
            ]);

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee ' . $request->name . ' has been added successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to save employee: ' . $e->getMessage()]);
        }
    }

    // View specific employee details and attendance log
    public function show(int $id): View
    {
        $employee = Employee::with(['department', 'user'])->findOrFail($id);

        $attendances = $employee->attendances()
            ->latest('date')
            ->paginate(15);

        $totalPresent = $employee->attendances()->where('status', 'present')->count();
        $totalHours = round($employee->attendances()->sum('total_hours'), 2);

        return view('admin.employees.show', compact('employee', 'attendances', 'totalPresent', 'totalHours'));
    }

    // Show form to edit employee
    public function edit(int $id): View
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    // Update existing employee and linked user account
    public function update(UpdateEmployeeRequest $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        DB::beginTransaction();

        try {
            // Update linked user account
            if ($employee->user) {
                $userUpdateData = [
                    'name' => $request->name,
                    'email' => $request->email,
                ];

                if ($request->filled('password')) {
                    $userUpdateData['password'] = Hash::make($request->password);
                }

                $employee->user->update($userUpdateData);
            }

            // Update employee record
            $employeeUpdateData = [
                'department_id' => $request->department_id,
                'employee_code' => $request->filled('employee_code') ? $request->employee_code : $employee->employee_code,
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $employeeUpdateData['initial_password'] = $request->password;
            }

            $employee->update($employeeUpdateData);

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee ' . $employee->name . ' updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to update employee: ' . $e->getMessage()]);
        }
    }

    // Toggle active or inactive status
    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
            $employee->status = $newStatus;
            $employee->save();

            $statusText = ucfirst($newStatus);
            return back()->with('success', "Employee {$employee->name} is now {$statusText}.");

        } catch (Exception $e) {
            return back()->withErrors(['general' => 'Failed to change employee status.']);
        }
    }

    // Soft delete employee and associated user account
    public function destroy(int $id): RedirectResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $name = $employee->name;

            // Soft delete user account if exists
            if ($employee->user) {
                $employee->user->delete();
            }

            // Soft delete employee record
            $employee->delete();

            return redirect()->route('admin.employees.index')->with('success', "Employee {$name} soft deleted successfully.");

        } catch (Exception $e) {
            return back()->withErrors(['general' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }

    // Generate next sequential unique employee code
    protected function generateNextEmployeeCode(): string
    {
        $maxId = Employee::max('id') ?? 0;
        $nextNumber = $maxId + 1;

        do {
            $code = 'EMP' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $exists = Employee::where('employee_code', $code)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $code;
    }
}
