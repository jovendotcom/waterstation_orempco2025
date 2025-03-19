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
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Validation\Rule;
use App\Exports\StocksCountExport;
use PDF;
use App\Exports\StocksCountExportAdmin;
use App\Exports\CustomersExportAdmin;
use Illuminate\Support\Carbon;
use App\Exports\SalesReportExportAdmin;

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
        // Fetch sales transactions with 'credit' payment method and 'Not Paid' remarks
        $sales = SalesTransaction::with(['customer', 'staff', 'salesItems'])
            ->where('payment_method', 'credit')
            ->where('remarks', 'Not Paid') // Add this condition
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
    
        $sale->update(['remarks' => 'Paid']);
        $sale->save();
    
        return response()->json(['success' => true, 'message' => 'Sale marked as paid successfully.']);
    }


    public function productInventory(Request $request)
    {
        $products = ProductForSale::all();
        $stocks = StocksCount::all(); // Fetch available stock items
        $subcategories = Subcategory::all(); // Fetch all subcategories
        $sizeOptions = ['Small', 'Medium', 'Large', 'Solo', 'Jumbo']; // Example size options
        $unitsOfMeasurement = ['kg', 'g', 'L', 'mL', 'pcs']; // Example units of measurement
        $categories = Category::with('stockCounts')->get();
    
        return view('admin.product_inventory', compact('products', 'stocks', 'subcategories', 'sizeOptions', 'unitsOfMeasurement', 'categories'));
    }

    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quantity' => 'nullable|integer|min:0',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'items_needed' => 'nullable|array',
            'material_quantities' => 'nullable|array',
            'material_quantity_unit_of_measurement' => 'nullable|array', // Add this line
            'subcategory_id' => 'required|exists:subcategories,id',
            'size_options' => 'nullable|string',
            'unit_of_measurement' => 'nullable|string',
        ]);
    
        // Check if the product with the same name and price already exists
        $existingProduct = ProductForSale::where('product_name', $request->product_name)
                                          ->where('price', $request->price)
                                          ->first();
    
        if ($existingProduct) {
            return redirect()->back()->with('fail', 'A product with the same name and price already exists.');
        }
    
        // Handle product image upload
        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
        }
    
        // Decode selected items
        $itemsNeeded = $request->items_needed ?? [];
        $materialQuantities = $request->material_quantities ?? [];
        $materialQuantityUnits = $request->material_quantity_unit_of_measurement ?? [];
    
        // Check stock availability
        $productQuantity = $request->quantity ?? null;
        foreach ($itemsNeeded as $stockId => $stockName) {
            $stock = StocksCount::find($stockId);
    
            if (!$stock) {
                return redirect()->back()->with('fail', "Stock item '$stockName' not found.");
            }
    
            if ($productQuantity !== null && $stock->quantity < $productQuantity) {
                return redirect()->back()->with('fail', "Not enough stock for '$stock->item_name'. Available: $stock->quantity, Required: $productQuantity.");
            }
        }
    
        // Create new product
        $product = ProductForSale::create([
            'product_name' => $request->product_name,
            'price' => $request->price,
            'quantity' => $productQuantity,
            'product_image' => $imagePath,
            'items_needed' => json_encode($itemsNeeded),
            'material_quantities' => json_encode($materialQuantities),
            'material_quantity_unit_of_measurement' => json_encode($materialQuantityUnits), // Add this line
            'subcategory_id' => $request->subcategory_id,
            'size_options' => $request->size_options,
            'unit_of_measurement' => $request->unit_of_measurement,
        ]);
    
        // Deduct stock quantities if product has a set quantity
        if ($productQuantity !== null) {
            foreach ($itemsNeeded as $stockId => $stockName) {
                $stock = StocksCount::find($stockId);
                if ($stock) {
                    $stock->quantity -= $productQuantity;
                    $stock->save();
                }
            }
        }
    
        return redirect()->back()->with('success', 'Product added successfully.');
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

    public function updateProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_for_sale,id',
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'size_options' => 'nullable|string',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'items_needed' => 'nullable|array',
            'material_quantities' => 'nullable|array',
        ]);
    
        $product = ProductForSale::find($request->product_id);
    
        // Handle product image upload
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
            $product->product_image = $imagePath;
        }
    
        $product->update([
            'product_name' => $request->product_name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'subcategory_id' => $request->subcategory_id,
            'size_options' => $request->size_options,
            'items_needed' => json_encode($request->items_needed),
            'material_quantities' => json_encode($request->material_quantities),
        ]);
    
        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function getCategories()
    {   

        $categories = Category::all();
        $subcategories = Subcategory::with('category')->get();

        return view("admin.category_subcategory" , compact('categories', 'subcategories'));
    
    }

    public function storeCategories(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'The Category Name field is required.',
            'name.string' => 'The Category Name must be a string.',
            'name.max' => 'The Category Name must not exceed 255 characters.',
            'name.unique' => 'The Category Name already exists.',
        ]);
    
        Category::create($request->only('name'));
    
        return redirect()->route('admin.categories')->with('success', 'Category added successfully!');
    }
    
    public function updateCategories(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:categories,name,' . $request->id,
        ], [
            'id.required' => 'The Category ID is required.',
            'id.exists' => 'The selected Category does not exist.',
            'name.required' => 'The Category Name field is required.',
            'name.string' => 'The Category Name must be a string.',
            'name.max' => 'The Category Name must not exceed 255 characters.',
            'name.unique' => 'The Category Name already exists.',
        ]);
    
        $category = Category::find($request->id);
        $category->update($request->only('name'));
    
        return redirect()->route('admin.categories')->with('success', 'Category updated successfully!');
    }

    // Delete Category
    public function destroyCategories($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully!');
    }

    public function storeSubCategories(Request $request)
    {
        $request->validate([
            'sub_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                }),
            ],
            'category_id' => 'required|exists:categories,id',
        ], [
            'sub_name.required' => 'The Subcategory Name field is required.',
            'sub_name.unique' => 'The Subcategory Name already exists for this category.',
            'category_id.required' => 'The Parent Category field is required.',
            'category_id.exists' => 'The selected Parent Category does not exist.',
        ]);
    
        Subcategory::create($request->only('sub_name', 'category_id'));
    
        return redirect()->route('admin.categories')->with('success', 'Subcategory added successfully!');
    }
    
    public function updateSubCategories(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:subcategories,id',
            'sub_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                })->ignore($request->id),
            ],
            'category_id' => 'required|exists:categories,id',
        ], [
            'sub_name.required' => 'The Subcategory Name field is required.',
            'sub_name.unique' => 'The Subcategory Name already exists for this category.',
            'category_id.required' => 'The Parent Category field is required.',
            'category_id.exists' => 'The selected Parent Category does not exist.',
        ]);
    
        $subcategory = Subcategory::find($request->id);
        $subcategory->update($request->only('sub_name', 'category_id'));
    
        return redirect()->route('admin.categories')->with('success', 'Subcategory updated successfully!');
    }

    // Delete Subcategory
    public function destroySubCategories($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $subcategory->delete();

        return redirect()->route('admin.categories')->with('success', 'Subcategory deleted successfully!');
    }

    public function userProfile()
    {
        // Retrieve the currently authenticated user
        $user = Auth::guard('admin')->user();
        return view('admin.user_profile', compact('user'));
    }

    public function changePassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('admin')->user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('adminlogin')->with('success', 'Password updated successfully. Please re-login.');
    }

    public function countStocks(Request $request)
    {
        $stocks = StocksCount::orderBy('item_name', 'asc')->get(); // Fetch all stock counts
        $categories = Category::all();

        // Define possible units of measurement
        $unitsOfMeasurement = [
            'grams' => 'Grams',
            'ml' => 'Milliliters',
            'pieces' => 'Pieces',
        ];
        return view('admin.stocks_count', compact('stocks', 'categories', 'unitsOfMeasurement'));
    }

    public function storeStockCount(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_of_measurement' => 'required|string|max:50', // Add this
            'price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
        ]);
    
        StocksCount::create([
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'unit_of_measurement' => $request->unit_of_measurement, // Add this
            'price' => $request->price,
            'remarks' => $request->remarks,
            'category_id' => $request->category_id,
        ]);
    
        return redirect()->route('admin.stocksCount')->with('success', 'Stock added successfully!');
    }

    // Update stock count
    public function updateStockCount(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $stock = StocksCount::where('item_name', $request->item_name)->first();
        if ($stock) {
            $stock->quantity += $request->quantity;  // Adding to the current stock
            $stock->price += $request->price;
            $stock->save();

            return redirect()->route('admin.stocksCount')->with('success', 'Stock updated successfully!');
        }

        return redirect()->route('admin.stocksCount')->with('fail', 'Stock not found!');
    }

    public function exportStocks($format)
    {
        $categories = Category::with('stockCounts')->get();
    
        if ($categories->isEmpty() || $categories->every(fn($category) => $category->stockCounts->isEmpty())) {
            return redirect()->back()->with('fail', 'No stock data found to export.');
        }
    
        if ($format === 'excel') {
            return Excel::download(new StocksCountExportAdmin, 'physical_inventory_count_form.xlsx');
        } elseif ($format === 'pdf') {
            $pdf = \PDF::loadView('exports.stocks_count_admin', compact('categories'))
                ->setPaper('legal', 'portrait');
            return $pdf->download('physical_inventory_count_form.pdf');
        } else {
            return redirect()->back()->with('fail', 'Invalid export format selected.');
        }
    }

    //for customer list
    public function customerList()
    {   
        // Fetch distinct departments and order them alphabetically
        $departments = Customer::distinct()->orderBy('department')->pluck('department');

        $customers = Customer::orderBy('full_name')->get();
        
        return view('admin.customer_list', compact('customers', 'departments'));
    }

    // Controller Method for Storing Employee
    public function storeEmployeeAdmin(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|string|max:255|unique:customers,employee_id',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'department' => 'required|string|max:255',
            'membership_status' => 'required|in:Member,Non-Member',
        ], [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique' => 'This Employee ID is already taken.',
            'last_name.required' => 'Last name is required.',
            'first_name.required' => 'First name is required.',
            'department.required' => 'Department is required.',
            'membership_status.required' => 'Membership status is required.',
        ]);

        // Combine full name
        $fullName = strtoupper($validatedData['last_name']) . ', ' . strtoupper($validatedData['first_name']);
        if (!empty($validatedData['middle_initial'])) {
            $fullName .= ' ' . strtoupper($validatedData['middle_initial']) . '.';
        }

        // Save Employee
        Customer::create([
            'type' => 'Employee',
            'employee_id' => $validatedData['employee_id'],
            'full_name' => $fullName,
            'department' => $validatedData['department'],
            'membership_status' => $validatedData['membership_status'],
        ]);

        return redirect()->back()->with('success', 'Employee saved successfully!');
    }

    // Store Department
    public function storeDepartment(Request $request)
    {
        $validatedData = $request->validate([
            'department_name' => 'required|string|max:255|unique:customers,full_name',
            'membership_status' => 'required|in:Member,Non-Member',
        ]);

        // Save Department
        Customer::create([
            'type' => 'Department',
            'full_name' => strtoupper($validatedData['department_name']),
            'department' => strtoupper($validatedData['department_name']),
            'membership_status' => $validatedData['membership_status'],
        ]);

        return redirect()->back()->with('success', 'Department saved successfully!');
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
            return Excel::download(new CustomersExportAdmin($customers), 'orempco_customers_list.xlsx');
        } elseif ($format === 'pdf') {
            $pdf = PDF::loadView('exports.customer_list_admin', compact('customers'))
                      ->setPaper('legal', 'portrait'); // Use Legal size in portrait mode
    
            return $pdf->download('orempco_customers_list.pdf');
        }
    
        return redirect()->back()->with('error', 'Invalid export format.');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
    
        if ($customer->type === 'Employee') {
            // Handle Employee type
            $validatedData = $request->validate([
                'department' => 'required|string|max:255',
                'membership_status' => 'required|in:Member,Non-Member',
            ]);
            $customer->update([
                'department' => $validatedData['department'],
                'membership_status' => $validatedData['membership_status'],
            ]);
        } elseif ($customer->type === 'Department') {
            // Handle Department type
            $validatedData = $request->validate([
                'department_name' => 'required|string|max:255', // Validate department name
            ]);
    
            // Get the old department name
            $oldDepartmentName = $customer->full_name;
    
            // Update the department's full_name and department columns
            $customer->update([
                'full_name' => $validatedData['department_name'], // Update full_name with new department name
                'department' => $validatedData['department_name'], // Update department column
            ]);
    
            // Cascade the update to all employees under this department
            Customer::where('department', $oldDepartmentName)
                ->where('type', 'Employee')
                ->update(['department' => $validatedData['department_name']]);
        } elseif ($customer->type === 'Outside' || $customer->membership_status === 'Non-Member') {
            // Handle Outside or Non-Member type
            $validatedData = $request->validate([
                'outside_last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
                'outside_first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
                'outside_middle_initial' => ['nullable', 'string', 'max:1', 'regex:/^[a-zA-Z]$/'],
            ]);
    
            // Combine full name
            $fullName = strtoupper($validatedData['outside_last_name']) . ', ' . strtoupper($validatedData['outside_first_name']);
            if (!empty($validatedData['outside_middle_initial'])) {
                $fullName .= ' ' . strtoupper($validatedData['outside_middle_initial']) . '.';
            }
    
            $customer->update([
                'full_name' => $fullName,
            ]);
        }
    
        return redirect()->route('admin.customerlist')->with('success', 'Customer updated successfully!');
    }
    

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
    
        // Pass the customer name to the session or as  part of the redirect data
        return redirect()->route('admin.customerlist')
            ->with('success', 'Customer ' . $customer->full_name . ' deleted successfully!');
    }

    //for sales report
    public function getReports()
    {
        $sales = SalesTransaction::with(['staff', 'customer'])->latest()->get();
        return view('admin.sales_report', compact('sales'));
    }

    public function exportExcel(Request $request)
    {
        $fromDate = $request->input('from_date', now()->subMonth()->toDateString()); 
        $toDate = $request->input('to_date', now()->toDateString());

        return Excel::download(new SalesReportExportAdmin($fromDate, $toDate), 'OREMPCO_Sales_Report.xlsx');
    }
    
    public function exportPdf(Request $request)
    {
        // Validate date input
        $fromDate = $request->input('from_date') ? Carbon::parse($request->input('from_date'))->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $toDate = $request->input('to_date') ? Carbon::parse($request->input('to_date'))->endOfDay() : Carbon::now()->endOfDay();
    
        // Fetch sales data with related models
        $sales = SalesTransaction::whereBetween('created_at', [$fromDate, $toDate])
            ->with('salesItems', 'staff', 'customer')
            ->get();
    
        // Generate Item Summary (Group by product name)
        $itemSummary = $sales->flatMap->salesItems->groupBy('product_name')->map(function ($items, $name) {
            return [
                'name' => $name,
                'quantity' => $items->sum('quantity'),
                'total' => $items->sum('subtotal')
            ];
        });
    
        // Generate Charge Summary (Payment breakdown)
        $chargeSummary = $sales->groupBy('payment_method')->map(function ($transactions, $method) {
            return [
                'total' => $transactions->sum(fn ($sale) => $sale->salesItems->sum('subtotal')),
                'member' => $transactions->where('customer.membership_status', 'Member')->sum(fn ($sale) => $sale->salesItems->sum('subtotal')),
                'non_member' => $transactions->where('customer.membership_status', 'Non-Member')->sum(fn ($sale) => $sale->salesItems->sum('subtotal'))
            ];
        });
    
        // Format dates for the filename
        $formattedFromDate = Carbon::parse($fromDate)->format('Y-m-d');
        $formattedToDate = Carbon::parse($toDate)->format('Y-m-d');
    
        // Load view into PDF and pass variables
        $pdf = Pdf::loadView('exports.sales_report_admin', compact('sales', 'itemSummary', 'chargeSummary', 'fromDate', 'toDate'))
            ->setPaper('A4', 'landscape')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('margin-left', '10mm')
            ->setOption('margin-right', '10mm');
    
        return $pdf->download("OREMPCO - Sales Report ({$formattedFromDate} to {$formattedToDate}).pdf");
    }

}
