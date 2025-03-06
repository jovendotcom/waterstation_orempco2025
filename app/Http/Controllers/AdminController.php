<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductForSale;
use App\Models\StocksCount;
use App\Models\Customer;
use App\Models\UserLog;
use App\Models\SalesTransaction;
use App\Models\SalesItem;
use Session;
use App\Models\MaterialRequirement;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function adminlogin()
    {
        return view("admin.adminlogin");
    }

    public function login_admin(Request $request){
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
                return redirect()->route('admin.dashboard')->with('success', 'Login Successful');
            } else {
                return back()->with('fail', 'Incorrect Password!');
            }
        } else {
            return back()->with('fail', 'Username is not registered!');
        }
    }

        // Authenticate the inventory user
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

        // Check if the user has the 'Admin' role
        if ($user->user_role !== 'admin') {
            return back()->with('fail', 'You are not authorized to access this section.');
        } else if ($user->user_status !== 'Active') {
            return back()->with('fail', 'This account is inactive, please contact the administrator.');
        }

        if (Auth::guard('admin')->attempt($credentials)) {

            // Store the logged-in user's ID in the session
            $request->session()->put('loginId', $user->id);

            return redirect()->route('admin.dashboard')->with([
                'success' => 'Login Successful',
                'full_name' => $user->full_name
            ]);
        }

        // If password is incorrect
        return back()->with('fail', 'The password is incorrect.');
    }

        // Admin Logout function
    public function adminlogout(Request $request)
    {   
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('adminlogin')->with('success', 'You have been logged out successfully.');
    }

    public function adminDashboard(Request $request)
    {
        $dailySales = DB::table('sales_transactions')
                        ->whereDate('created_at', today())
                        ->sum('total_amount');
    
        $monthlySales = DB::table('sales_transactions')
                          ->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year)
                          ->sum('total_amount');
    
        $totalTransactions = DB::table('sales_transactions')->count();
    
        $salesGraphData = DB::table('sales_transactions')
                            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total_sales'))
                            ->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->groupBy(DB::raw('DATE(created_at)'))
                            ->orderBy('date')
                            ->get();
    
        return view('admin.admin_dashboard', compact('dailySales', 'monthlySales', 'totalTransactions', 'salesGraphData'));
    }
    

    public function userManagement()
    {
        $users = User::with(['logs'])->get(); // Retrieve all users
        $userId = Session::get('loginId');
    
        if ($userId) {
            $loggedInUser = User::find($userId);
        } else {
            return redirect('adminlogin')->with('fail', 'You must be logged in.');
        }
    
        return view("admin.user_management", compact('users', 'loggedInUser'));
    }

    //function for adding users
    public function add_user(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'employeeId' => 'required|string|max:255|unique:users,emp_id',
            'fullName' => 'required|string|max:255',
            'role' => 'required|in:salesclerk',
            'status' => 'required|in:Active,Inactive',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:5|max:12|confirmed',
        ], [
            // Custom error messages (optional)
            'employeeId.required' => 'Employee ID is required.',
            'employeeId.unique' => 'This Employee ID is already in use.',
            'password.confirmed' => 'Password do not match.',
            // Add more custom messages as needed
        ]);
    
        // Create and save the new user
        $user = new User();
        $user->emp_id = $validatedData['employeeId'];
        $user->full_name = $validatedData['fullName'];
        $user->user_role = $validatedData['role'];
        $user->user_status = $validatedData['status'];
        $user->username = $validatedData['username'];
        $user->password = Hash::make($validatedData['password']);
        $user->save();
    
        // Redirect back with success message
        return redirect()->back()->with('success', 'User added successfully!');
    }

    public function edit_user(Request $request, $id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);
    
        // Validate the request data
        $validatedData = $request->validate([
            'fullName' => 'required|string|max:255',
            'role' => 'required|in:salesclerk',
            'status' => 'required|in:Active,Inactive',
            'username' => 'required|string|max:255|unique:users,username,' . $id,  // Add validation for the username
        ]);
    
        // Update the user
        $user->full_name = $validatedData['fullName'];
        $user->user_role = $validatedData['role'];
        $user->user_status = $validatedData['status'];
        $user->username = $validatedData['username']; 
        $user->save();
    
        // Redirect back with success message
        return redirect()->back()->with('success', 'User updated successfully!');
    }

    public function change_password(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'newPassword' => 'required|string|min:5|max:12|confirmed',
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the user's password
        $user->password = Hash::make($validatedData['newPassword']);
        $user->save();

        // Redirect back with success message
        return redirect()->back()->with('success', 'Password changed successfully!');
    }

    public function getCreditSales()
    {
        $sales = SalesTransaction::with(['customer', 'staff', 'salesItems'])
            ->where('payment_method', 'credit')
            ->get();
    
        return view('admin.credit_sales', compact('sales'));
    }

    public function getSaleItems($id)
    {
        $sale = SalesTransaction::with('salesItems')->find($id);
    
        if (!$sale) {
            return response()->json([], 404);
        }
    
        return response()->json($sale->salesItems);
    } 

    public function markAsPaid($id)
    {
        $sale = SalesTransaction::find($id);
        
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.']);
        }

        $sale->remarks = 'Paid';
        $sale->save();

        return response()->json(['success' => true, 'message' => 'Sale marked as paid successfully.']);
    }


    public function productInventory(Request $request)
    {
        $products = ProductForSale::all();
        $stocks = StocksCount::all(); // Fetch available stock items
    
        return view('admin.product_inventory', compact('products', 'stocks'));
    } 

    public function addStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_for_sale,id',
            'add_quantity' => 'required|integer|min:1',
        ]);

        $product = ProductForSale::find($request->product_id);

        if ($product->quantity === null) {
            return redirect()->back()->with('fail', 'Stock cannot be added for this product.');
        }

        $product->quantity += $request->add_quantity;
        $product->save();

        return redirect()->back()->with('success', 'Stock updated successfully.');
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_for_sale,id',
            'new_price' => 'required|numeric|min:0',
        ]);

        $product = ProductForSale::find($request->product_id);
        $product->price = $request->new_price;
        $product->save();

        return redirect()->back()->with('success', 'Price updated successfully.');
    }
}
