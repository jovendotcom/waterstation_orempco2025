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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use PDF;

class CustomerListController extends Controller
{

    public function store(Request $request)
    {
        // Validation
        $validatedData = $request->validate([
            'type' => 'required|in:Department,Employee',
            'department' => 'required_if:type,Department|string|max:255',

            // For Employee
            'employee_id' => 'nullable|required_if:type,Employee|string|max:255',
            'last_name' => 'nullable|required_if:type,Employee|string|max:255',
            'first_name' => 'nullable|required_if:type,Employee|string|max:255',
            'middle_initial' => 'nullable|string|max:1',

            // Membership status (for Employee and Department)
            'membership_status' => 'required|in:Member,Non-Member',
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
            'middle_initial.max' => 'Middle initial must not exceed 1 character.',

            'membership_status.required' => 'Please select membership status.',
            'membership_status.in' => 'Invalid membership status selected.',
        ]);

        // Data Handling
        if ($request->type === 'Department') {
            $department = strtoupper($request->department);

            // Check for existing department
            $existingDepartment = Customer::where('type', 'Department')
                ->whereRaw('UPPER(full_name) = ?', [$department])
                ->first();

            if ($existingDepartment) {
                return redirect()
                    ->back()
                    ->withInput($request->except('department'))
                    ->withErrors(['department' => 'This department already exists.']);
            }

            $fullName = $department;
            $employeeId = null;

        } elseif ($request->type === 'Employee') {
            // Check for existing employee
            $existingEmployee = Customer::where('employee_id', $request->employee_id)->first();

            if ($existingEmployee) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['employee_id' => 'This Employee ID already exists.']);
            }

            $lastName = strtoupper($request->last_name);
            $firstName = strtoupper($request->first_name);
            $middleInitial = strtoupper($request->middle_initial);

            $fullName = $lastName . ', ' . $firstName;
            $fullName .= $middleInitial ? ' ' . $middleInitial . '.' : '';

            $employeeId = $request->employee_id;
            $department = $request->department;
        }

        // Save Customer
        Customer::create([
            'type' => $request->type,
            'employee_id' => $employeeId,
            'full_name' => $fullName,
            'department' => $department,
            'membership_status' => $request->membership_status,
        ]);

        return redirect()->back()->with('success', 'Customer saved successfully!');
    }
    
    public function storeOutside(Request $request)
    {
        $validated = $request->validate([
            'outside_membership_status' => 'required|in:Member,Non-Member',
            'outside_last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'outside_first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'outside_middle_initial' => ['nullable', 'string', 'max:1', 'regex:/^[a-zA-Z]$/'],
        ], [
            'outside_last_name.required' => 'The last name is required.',
            'outside_last_name.regex' => 'The last name should only contain letters and spaces.',
            'outside_first_name.required' => 'The first name is required.',
            'outside_first_name.regex' => 'The first name should only contain letters and spaces.',
            'outside_middle_initial.regex' => 'The middle initial should be a single letter.',
        ]);
    
        // Combine full name
        $full_name = strtoupper($validated['outside_last_name']) . ', ' . strtoupper($validated['outside_first_name']);
        if (!empty($validated['outside_middle_initial'])) {
            $full_name .= ' ' . strtoupper($validated['outside_middle_initial']) . '.';
        }
    
        // Create and save the outside customer
        $customer = new Customer();
        $customer->full_name = $full_name;
        $customer->department = 'NONE';          // Store as 'NONE'
        $customer->employee_id = null;         // Store as 'NONE'
        $customer->type = 'Outside';             // Correct type
        $customer->membership_status = $validated['outside_membership_status'];
        $customer->save();
    
        return redirect()->back()->with('success', 'Outside customer added successfully.');
    }
    
    public function export($format)
    {
        $customers = Customer::all();

        if ($format === 'excel') {
            return Excel::download(new CustomersExport($customers), 'orempco_waterstation_customers_list.xlsx');
        } elseif ($format === 'pdf') {
            $pdf = PDF::loadView('exports.customers', compact('customers'));
            return $pdf->download('orempco_waterstation_customers_list.pdf');
        }

        return redirect()->back()->with('error', 'Invalid export format.');
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
