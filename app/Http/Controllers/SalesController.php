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
                return redirect()->route('sales.transaction');
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
        $products = ProductForSale::all(); // Fetch all products
    
        // Fetch customers and order them based on the 'type' column
        $customers = Customer::orderByRaw("FIELD(type, 'department', 'employee')")
                             ->orderBy('full_name', 'asc') // Then order alphabetically by full_name
                             ->get(); // Fetch customers
    
        // Generate PO number
        $poNumber = 'PO-' . strtoupper(uniqid());
        
        return view('sales.sales_transaction', compact('products', 'customers', 'poNumber')); // Pass both products and customers to the view
    }
    

    public function newPo()
    {
        $poNumber = 'PO-' . strtoupper(uniqid());
        return response()->json(['po_number' => $poNumber]);
    }


    public function store(Request $request)
    {
        // Debugging - log request data
        \Log::info('Sales transaction request:', $request->all());
    
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,credit',
            'total_amount' => 'required|numeric|min:0',
            'total_items' => 'required|numeric|min:1',
            'cart' => 'required|array',
        ]);
    
        // Ensure cart has valid product data
        foreach ($request->cart as $item) {
            if (!isset($item['id'], $item['name'], $item['qty'], $item['price'], $item['subtotal'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid product data',
                ], 422);
            }
        }
    
        try {
            DB::beginTransaction();
    
            // Create sales transaction
            $sale = SalesTransaction::create([
                'po_number' => $request->po_number,
                'customer_id' => $request->customer_id,
                'staff_id' => Auth::guard('sales')->user()->id,
                'payment_method' => $request->payment_method,
                'total_amount' => $request->total_amount,
                'amount_tendered' => $request->amount_tendered ?? null,
                'change_amount' => $request->change_amount ?? null,
                'credit_payment_method' => $request->charge_to ?? null,
                'total_items' => $request->total_items,
            ]);
    
            // Process each item in the cart
            foreach ($request->cart as $item) {
                $product = ProductForSale::find($item['id']);
                $quantityPurchased = $item['qty'];
    
                if ($product) {
                    // Check if product has stock in 'products_for_sale'
                    if (!is_null($product->quantity) && $product->quantity > 0) {
                        // If stock available in 'products_for_sale', deduct from there
                        if ($product->quantity >= $quantityPurchased) {
                            // Deduct from the product's stock in 'products_for_sale'
                            $product->quantity -= $quantityPurchased;
                            $product->save();
                        } else {
                            // Insufficient stock in 'products_for_sale', return error
                            return response()->json([
                                'success' => false,
                                'message' => "Insufficient stock for {$product->product_name}.",
                                'available_stock' => $product->quantity,
                            ], 422);
                        }
                    } else {
                        // If stock is NULL or 0, check in 'StocksCount' and deduct from there
                        if (!empty($product->items_needed)) {
                            $itemsNeeded = json_decode($product->items_needed, true); // Convert JSON to array
                            $errorMessages = [];
    
                            foreach ($itemsNeeded as $neededItemId => $neededItemName) {
                                $neededStock = StocksCount::where('item_name', $neededItemName)->first();
    
                                if ($neededStock && $neededStock->quantity >= $quantityPurchased) {
                                    // Deduct from the needed item's stock in 'StocksCount'
                                    $neededStock->quantity -= $quantityPurchased;
                                    $neededStock->save();
                                    break; // Stop once we successfully deducted
                                } else {
                                    // Track missing items if insufficient stock
                                    $errorMessages[] = "{$neededItemName} is out of stock. Available: " . ($neededStock ? $neededStock->quantity : 0);
                                }
                            }
    
                            // If any item_needed is missing, show an error
                            if (count($errorMessages) > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "Transaction failed due to insufficient stock for the following items: " . implode(', ', $errorMessages),
                                ], 422);
                            }
                        }
                    }
                }
    
                // Insert into SalesItem table
                SalesItem::create([
                    'sales_transaction_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Transaction completed successfully!',
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sales transaction failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function salesHistory(Request $request)
    {
        $sales = SalesTransaction::with('customer')->get(); // Eager load customer
    
        return view('sales.sales_history', compact('sales'));
    }
    
    
    
    public function customerList()
    {   
        // Fetch distinct departments and order them alphabetically
        $departments = Customer::distinct()->orderBy('department')->pluck('department');

        $customers = Customer::orderBy('full_name')->get();
        
        return view('sales.customer_list', compact('customers', 'departments'));
    }
}
