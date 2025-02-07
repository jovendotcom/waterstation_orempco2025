<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use App\Models\UserLog;
use Illuminate\Validation\Rule;
use Session;

class CustomerListController extends Controller
{

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|in:Department,Employee',
            'department' => 'required_if:type,Department|string|max:255',
            'employee_id' => 'nullable|required_if:type,Employee|string|max:255',
            'last_name' => 'nullable|required_if:type,Employee|string|max:255',
            'first_name' => 'nullable|required_if:type,Employee|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
        ], [
            'type.required' => 'Please select the type of customer (Employee or Department).',
            'type.in' => 'Invalid type selected.',
            'department.required_if' => 'Department name is required for Department type.',
            'department.max' => 'Department name must not exceed 255 characters.',
            'employee_id.required_if' => 'Employee ID is required for Employee type.',
            'employee_id.max' => 'Employee ID must not exceed 255 characters.',
            'last_name.required_if' => 'Last name is required for Employee type.',
            'last_name.max' => 'Last name must not exceed 255 characters.',
            'first_name.required_if' => 'First name is required for Employee type.',
            'first_name.max' => 'First name must not exceed 255 characters.',
            'middle_initial.required_if' => 'Middle initial is required for Employee type.',
            'middle_initial.max' => 'Middle initial must not exceed 1 character.',
        ]);
    
        $lastName = strtoupper($request->last_name);
        $firstName = strtoupper($request->first_name);
        $middleInitial = strtoupper($request->middle_initial);
        $department = strtoupper($request->department);
    
        if ($request->type === 'Department') {
            $existingDepartment = Customer::where('type', 'Department')
                ->whereRaw('UPPER(full_name) = ?', [$department])
                ->first();
    
            if ($existingDepartment) {
                return redirect()
                    ->back()
                    ->withInput($request->except('department')) // Use this to exclude 'department' field from old input and force the field to remain empty for correction
                    ->withErrors(['department' => 'This department already exists.']);
            }
    
            $fullName = $department;
            $employeeId = null;
        } elseif ($request->type === 'Employee') {
            $existingEmployee = Customer::where('employee_id', $request->employee_id)->first();
    
            if ($existingEmployee) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['employee_id' => 'This Member ID already exists.']);
            }
    
            $fullName = strtoupper($lastName) . ', ' . strtoupper($firstName);
            $fullName .= $middleInitial ? ' ' . strtoupper($middleInitial) . '.' : ''; // Adds middle initial and period if provided
            $employeeId = $request->employee_id;
        }
    
        Customer::create([
            'type' => $request->type,
            'employee_id' => $employeeId,
            'full_name' => $fullName,
            'department' => $department,
        ]);
    
        return redirect()->back()->with('success', 'Customer saved successfully!');
    }
         
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
    
        if ($customer->type === 'Employee') {
            $validatedData = $request->validate([
                'department' => 'required|string|max:255',
            ]);
            $customer->update([
                'department' => $validatedData['department'],
            ]);
        } elseif ($customer->type === 'Department') {
            $validatedData = $request->validate([
                'full_name' => 'required|string|max:255',
            ]);
            $customer->update([
                'full_name' => $validatedData['full_name'],
            ]);
        }
    
        return redirect()->route('sales.customerlist')->with('success', 'Customer updated successfully!');
    }
    

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
    
        // Pass the customer name to the session or as part of the redirect data
        return redirect()->route('sales.customerlist')
            ->with('success', 'Customer ' . $customer->full_name . ' deleted successfully!');
    }
    

    
    
}
