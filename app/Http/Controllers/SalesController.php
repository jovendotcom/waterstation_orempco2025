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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use App\Models\Subcategory;

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
        $products = ProductForSale::with('subcategory')->get(); 

        // Group products by subcategory
        $groupedProducts = $products->groupBy('subcategory_id');
    
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
    
        return view('sales.sales_transaction', compact('products', 'customers', 'poNumber', 'groupedProducts'));
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
            if (!isset($item['id'], $item['name'], $item['qty'], $item['price'], $item['subtotal'], $item['materialAdjustments'])) {
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
                    // If the product has a quantity, deduct from products_for_sale
                    if (!is_null($product->quantity)) {
                        if ($product->quantity >= $quantityPurchased) {
                            $product->quantity -= $quantityPurchased;
                            $product->save();
                        } else {
                            $errorMessages[] = "{$product->product_name} is out of stock. Needed: $quantityPurchased, Available: {$product->quantity}";
                        }
                    } else {
                        // If the product has no quantity, deduct materials from stocks_count
                        if (!empty($product->items_needed)) {
                            $itemsNeeded = json_decode($product->items_needed, true);
                            $materialQuantities = json_decode($product->material_quantities, true);
    
                            if (!is_array($itemsNeeded) || !is_array($materialQuantities)) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "Invalid format for items_needed or material_quantities in {$product->product_name}.",
                                ], 422);
                            }
    
                            foreach ($itemsNeeded as $neededItemId => $neededItemName) {
                                $material = StocksCount::where('item_name', $neededItemName)->first();
    
                                if (!$material) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Material '{$neededItemName}' not found in stock.",
                                    ], 422);
                                }
    
                                // Get the adjusted quantity from the cart
                                $adjustedQty = $item['materialAdjustments'][$neededItemName] ?? 1;
    
                                // Calculate the material quantity needed based on the adjusted quantity
                                $materialQtyNeeded = $materialQuantities[$neededItemId] * $adjustedQty;
    
                                if ($material->quantity >= $materialQtyNeeded) {
                                    $material->quantity -= $materialQtyNeeded;
                                    $material->save();
                                } else {
                                    $availableQty = $material->quantity;
                                    $errorMessages[] = "{$neededItemName} is out of stock. Needed: $materialQtyNeeded, Available: $availableQty";
                                }
                            }
                        }
                    }
                }
    
                // Create sales item record
                SalesItem::create([
                    'sales_transaction_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
    
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
        $loggedInAdmin = Auth::guard('sales')->user();
    
        if (!$loggedInAdmin) {
            return redirect()->route('saleslogin');
        }
    
        $sales = SalesTransaction::with(['customer', 'staff', 'salesItems'])
            ->where('payment_method', 'credit')
            ->where('staff_id', $loggedInAdmin->id)
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
        $loggedInAdmin = Auth::guard('sales')->user();
    
        if (!$loggedInAdmin) {
            return redirect()->route('saleslogin');
        }
    
        $sales = SalesTransaction::with(['customer', 'salesItems'])
            ->where('remarks', 'Paid')
            ->where('staff_id', $loggedInAdmin->id) 
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
        // Kunin ang naka-login na admin user
        $loggedInAdmin = Auth::guard('sales')->user();
    
        // Kung walang naka-login na admin, i-redirect sa login page
        if (!$loggedInAdmin) {
            return redirect()->route('admin.login'); // Palitan ng tamang route para sa admin login
        }
    
        // Kunin ang mga sales transactions kung saan ang staff ID ay katulad ng naka-login na admin
        $sales = SalesTransaction::with(['staff', 'customer'])
            ->where('staff_id', $loggedInAdmin->id) // I-filter base sa staff ID ng naka-login na admin
            ->latest()
            ->get();
    
        return view('sales.sales_report', compact('sales'));
    }

    public function exportExcel(Request $request)
    {
        // Kunin ang naka-login na admin user
        $loggedInAdmin = Auth::guard('sales')->user();
    
        // Kung walang naka-login na admin, i-redirect sa login page
        if (!$loggedInAdmin) {
            return redirect()->route('saleslogin'); // Palitan ng tamang route para sa admin login
        }
    
        // Kunin ang from_date at to_date mula sa request
        $fromDate = $request->input('from_date', now()->subMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
    
        // I-export ang sales report na nauugnay sa naka-login na admin
        return Excel::download(new SalesReportExport($fromDate, $toDate, $loggedInAdmin->id), 'OREMPCO_Sales_Report.xlsx');
    }
    
    public function exportPdf(Request $request)
    {
        $loggedInAdmin = Auth::guard('sales')->user();
    
        if (!$loggedInAdmin) {
            return redirect()->route('saleslogin');
        }
    
        // Validate date input
        $fromDate = $request->input('from_date') ? Carbon::parse($request->input('from_date'))->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $toDate = $request->input('to_date') ? Carbon::parse($request->input('to_date'))->endOfDay() : Carbon::now()->endOfDay();
    
        // Fetch sales data with related models, filtered by staff_id
        $sales = SalesTransaction::whereBetween('created_at', [$fromDate, $toDate])
            ->where('staff_id', $loggedInAdmin->id) // I-filter base sa staff ID ng naka-login na admin
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
        $pdf = Pdf::loadView('exports.sales_report', compact('sales', 'itemSummary', 'chargeSummary', 'fromDate', 'toDate'))
            ->setPaper('A4', 'landscape')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('margin-left', '10mm')
            ->setOption('margin-right', '10mm');
    
        return $pdf->download("OREMPCO - Sales Report ({$formattedFromDate} to {$formattedToDate}).pdf");
    }

    public function userProfile()
    {
        // Retrieve the currently authenticated user
        $user = Auth::guard('sales')->user();
        return view('sales.user_profile', compact('user'));
    }

    public function changePassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('sales')->user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('saleslogin')->with('success', 'Password updated successfully. Please re-login.');
    }
    
}
