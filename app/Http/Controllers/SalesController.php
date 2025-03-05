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

class SalesController extends Controller
{
    public function getMaterialRequirement($productId, $materialId)
    {
        try {
            $requirement = MaterialRequirement::where('product_id', $productId)
                ->where('material_id', $materialId)
                ->first();

            return $requirement ? $requirement->quantity_needed : 1; // Default to 1 if not found
        } catch (\Exception $e) {
            \Log::error("Error fetching material requirement: " . $e->getMessage());
            return 1;
        }
    }

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
        $products = ProductForSale::all(); 
    
        $customers = Customer::orderByRaw("FIELD(type, 'department', 'employee')")
                             ->orderBy('full_name', 'asc')
                             ->get(); 
    
        // Get the last PO number from sales_transactions
        $lastTransaction = \DB::table('sales_transactions')
            ->where('po_number', 'LIKE', 'SO-%')
            ->orderBy('id', 'desc')
            ->first();
    
        // If no previous transactions, start at 0001
        $lastNumber = $lastTransaction ? (int) substr($lastTransaction->po_number, 3) : 0;
        $newNumber = $lastNumber + 1;
    
        // Format as 4-digit number (e.g., SO-0001, SO-0002, ...)
        $poNumber = 'SO-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
        return view('sales.sales_transaction', compact('products', 'customers', 'poNumber'));
    }       
    

    public function newPo()
    {
        // Get last PO number from sales_transactions
        $lastTransaction = \DB::table('sales_transactions')
            ->where('po_number', 'LIKE', 'SO-%')
            ->orderBy('id', 'desc')
            ->first();
    
        // Extract last number and increment
        $lastNumber = $lastTransaction ? (int) substr($lastTransaction->po_number, 3) : 0;
        $newNumber = $lastNumber + 1;
    
        // Format as 4-digit number
        $poNumber = 'SO-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
        return response()->json(['po_number' => $poNumber]);
    }    


    public function store(Request $request)
    {
        \Log::info('Sales transaction request:', $request->all());
    
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,credit',
            'total_amount' => 'required|numeric|min:0',
            'total_items' => 'required|numeric|min:1',
            'cart' => 'required|array',
        ]);
    
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
    
            $remarks = $request->payment_method === 'cash' ? 'Paid' : 'Not Paid';
    
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
                'remarks' => $remarks,
            ]);
    
            $errorMessages = [];
    
            foreach ($request->cart as $item) {
                $product = ProductForSale::find($item['id']);
                $quantityPurchased = $item['qty'];
    
                if ($product) {
                    if (!empty($product->items_needed)) {
                        $itemsNeeded = json_decode($product->items_needed, true);
    
                        if (!is_array($itemsNeeded)) {
                            return response()->json([
                                'success' => false,
                                'message' => "Invalid format for items_needed in {$product->product_name}.",
                            ], 422);
                        }
    
                        foreach ($itemsNeeded as $neededItemId => $neededItemName) {
                            // ✅ Hanapin ang tamang material sa `StocksCount`
                            $material = StocksCount::where('item_name', $neededItemName)->first();
    
                            if (!$material) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "Material '{$neededItemName}' not found in stock.",
                                ], 422);
                            }
    
                            // ✅ KUNIN ANG TAMANG QUANTITY NG MATERIAL MULA SA FRONTEND CART
                            $materialQtyNeeded = $item['materials'][$neededItemName] ?? 1; // Get displayed quantity
    
                            // ✅ Check stock availability at ibawas ang tamang quantity
                            if ($material->quantity >= $materialQtyNeeded) {
                                $material->quantity -= $materialQtyNeeded;
                                $material->save();
                            } else {
                                // Kulang ang stock, mag-error
                                $availableQty = $material->quantity;
                                $errorMessages[] = "{$neededItemName} is out of stock. Needed: $materialQtyNeeded, Available: $availableQty";
                            }
                        }
                    }
                }
    
                SalesItem::create([
                    'sales_transaction_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
    
            // ❌ CANCEL TRANSACTION KUNG MAY KULANG NA MATERIALS
            if (!empty($errorMessages)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Transaction failed due to insufficient stock: " . implode(', ', $errorMessages),
                ], 422);
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
    
    public function getCreditSales()
    {
        $sales = SalesTransaction::with(['customer', 'staff', 'salesItems'])
            ->where('payment_method', 'credit')
            ->get();
    
        return view('sales.credit_sales', compact('sales'));
    }

    public function getSaleItems($id)
    {
        $sale = SalesTransaction::with('salesItems')->find($id);
    
        if (!$sale) {
            return response()->json([], 404);
        }
    
        return response()->json($sale->salesItems);
    }    
    

    
    public function salesHistory(Request $request)
    {
        // Eager load the customer and sales_items relationships
        $sales = SalesTransaction::with(['customer', 'salesItems'])
            ->where('remarks', 'Paid') // Filter only paid transactions
            ->get();
        
        return view('sales.sales_history', compact('sales'));
    }        
    
    
    public function customerList()
    {   
        // Fetch distinct departments and order them alphabetically
        $departments = Customer::distinct()->orderBy('department')->pluck('department');

        $customers = Customer::orderBy('full_name')->get();
        
        return view('sales.customer_list', compact('customers', 'departments'));
    }

    public function getReports()
    {
        $sales = SalesTransaction::with(['staff', 'customer'])->latest()->get();
        return view('sales.sales_report', compact('sales'));
    }

    public function exportExcel(Request $request)
    {
        $fromDate = $request->input('from_date', now()->subMonth()->toDateString()); 
        $toDate = $request->input('to_date', now()->toDateString());

        return Excel::download(new SalesReportExport($fromDate, $toDate), 'OREMPCO_Waterstation_Sales_Report.xlsx');
    }
}
