<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\UserLog;
use Session;

class SalesController extends Controller
{
    public function saleslogin()
    {
        return view("sales.saleslogin");
    }

    public function login_sales(Request $request){
        // Validate input
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:5|max:12',
        ]);

        // Check if the user exists
        $user = User::where('username', $request->username)->first();

        if ($user) {
            // Check if the password matches
            if (Hash::check($request->password, $user->password)) {
                // Store user ID in session
                $request->session()->put('loginId', $user->id);

                // Redirect to the dashboard after login
                return redirect()->route('sales.transaction')->with('success', 'Login Successful');
            } else {
                return back()->with('fail', 'Incorrect Password!');
            }
        } else {
            return back()->with('fail', 'Username is not registered!');
        }
    }

    // Authenticate the sales user
    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // Validate the request inputs
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Check if user exists with the username
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return back()->with('fail', 'No account found with the provided username.');
        }

        // Check if the user has the 'Sales Clerk' role
        if ($user->user_role !== 'salesclerk') {
            return back()->with('fail', 'You are not authorized to access this section.');
        } else if ($user->user_status !== 'Active') {
            return back()->with('fail', 'This account is inactive, please contact the administrator.');
        }

        if (Auth::guard('sales')->attempt($credentials)) {

            // Store the logged-in user's ID in the session
            $request->session()->put('loginId', $user->id);

            return redirect()->route('sales.transaction')->with([
                'success' => 'Login Successful',
                'full_name' => $user->full_name
            ]);
        }

        // If password is incorrect
        return back()->with('fail', 'The password is incorrect.');
    }

    // Sales Logout function
    public function saleslogout(Request $request)
    {   
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('saleslogin')->with('success', 'You have been logged out successfully.');
    }

    public function salesTransaction(Request $request)
    {
        $products = Product::all(); // Fetch all products
        $customers = Customer::orderBy('full_name', 'asc')->get(); // Fetch customers in alphabetical order
    
        return view('sales.sales_transaction', compact('products', 'customers')); // Pass both products and customers to the view
    }
    

    public function customerList()
    {   
        // Fetch distinct departments and order them alphabetically
        $departments = Customer::distinct()->orderBy('department')->pluck('department');

        $customers = Customer::orderBy('full_name')->get();
        
        return view('sales.customer_list', compact('customers', 'departments'));
    }
}
