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
use App\Models\MaterialInventory;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\Log;
use App\Models\SalesTransactionProcess;
use App\Models\SalesTransactionItem;

class SalesProcessingController extends Controller
{
    public function salesProcess()
    {
        // Fetch categories with their subcategories and products
        $categories = Category::with(['subcategories.product.materials'])->get();
        $customers = Customer::orderByRaw("FIELD(type, 'department', 'employee')")
                             ->orderBy('full_name', 'asc')
                             ->get(); 

        // Get the last PO number from sales_transactions
        $lastTransaction = \DB::table('sales_transactions_process')
        ->where('so_number', 'LIKE', 'SO-%')
        ->orderBy('id', 'desc')
        ->first();

        // If no previous transactions, start at 0001
        $lastNumber = $lastTransaction ? (int) substr($lastTransaction->so_number, 3) : 0;
        $newNumber = $lastNumber + 1;

        // Format as 4-digit number (e.g., SO-0001, SO-0002, ...)
        $soNumber = 'SO-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
        return view('sales.sales_processing', compact('categories', 'customers', 'soNumber'));
    }

    public function getMaterials($id)
    {
        try {
            // Find the product with its materials and include the pivot field
            $product = ProductInventory::with(['materials' => function ($query) {
                $query->select('materials_inventory.id', 'material_name', 'unit', 'cost_per_unit') // Include cost_per_unit
                      ->withPivot('quantity_used'); // Include the pivot field
            }])->find($id);
    
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }
    
            // Format the materials data
            $materials = $product->materials->map(function ($material) {
                return [
                    'id' => $material->id, // Include material ID
                    'material_name' => $material->material_name,
                    'quantity_used' => $material->pivot->quantity_used, // Access the pivot field
                    'unit' => $material->unit,
                    'cost_per_unit' => $material->cost_per_unit, // Include cost_per_unit
                ];
            });
    
            // Return the materials as JSON
            return response()->json($materials);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error fetching materials: ' . $e->getMessage());
    
            // Return an error response
            return response()->json(['error' => 'An error occurred while fetching materials'], 500);
        }
    }

    public function newSo()
    {
        // Get last PO number from sales_transactions
        $lastTransaction = \DB::table('sales_transactions_process')
            ->where('so_number', 'LIKE', 'SO-%')
            ->orderBy('id', 'desc')
            ->first();
    
        // Extract last number and increment
        $lastNumber = $lastTransaction ? (int) substr($lastTransaction->so_number, 3) : 0;
        $newNumber = $lastNumber + 1;
    
        // Format as 4-digit number
        $soNumber = 'SO-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
        return response()->json(['so_number' => $soNumber]);
    }  

    public function processCheckout(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,credit',
            'amount_tendered' => 'nullable|numeric|required_if:payment_method,cash',
            'change_amount' => 'nullable|numeric|required_if:payment_method,cash',
            'charge_to' => 'nullable|string|required_if:payment_method,credit',
            'total_items' => 'required|integer',
            'total_amount' => 'required|numeric',
            'cart' => 'required|array',
        ]);
    
        $lowStockMaterials = [];

        foreach ($request->cart as $item) {
            foreach ($item['materials'] as $material) {
                $materialInventory = MaterialInventory::find($material['id']);
        
                if ($materialInventory) {
                    $neededQty = $material['quantity_used'] * $item['quantity'];
                    $availableStock = $materialInventory->total_stocks;
        
                    if ($availableStock < $neededQty) {
                        $lowStockMaterials[] = [
                            'material_name' => $materialInventory->material_name,
                            'available' => $availableStock,
                            'needed' => $neededQty
                        ];
                    }
                }
            }
        }
        
        if (count($lowStockMaterials) > 0) {
            return response()->json([
                'success' => false,
                'low_stock' => true,
                'materials' => $lowStockMaterials
            ], 400);
        }
        

    
        // Step 2: Proceed with transaction and stock deduction
        DB::beginTransaction();
    
        try {
            $salesTransaction = SalesTransactionProcess::create([
                'so_number' => $request->so_number,
                'customer_id' => $request->customer_id,
                'sales_staff_id' => Auth::guard('sales')->user()->id,
                'payment_method' => $request->payment_method,
                'total_amount' => $request->total_amount,
                'amount_tendered' => $request->amount_tendered,
                'change_amount' => $request->change_amount,
                'charge_to' => $request->charge_to,
                'total_items' => $request->total_items,
                'remarks' => 'Paid',
            ]);
    
            foreach ($request->cart as $item) {
                SalesTransactionItem::create([
                    'sales_transaction_id' => $salesTransaction->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'materials_used' => json_encode($item['materials']),
                ]);
    
                foreach ($item['materials'] as $material) {
                    $materialInventory = MaterialInventory::find($material['id']);
                
                    if ($materialInventory) {
                        // Base material deduction
                        $deduction = $material['quantity_used'] * $item['quantity'];
                        $materialInventory->total_stocks -= $deduction;
                
                        // Additional materials deduction
                        if (!empty($material['additional'])) {
                            foreach ($material['additional'] as $additional) {
                                $additionalQty = $additional['quantity'];
                                $materialInventory->total_stocks -= $additionalQty;
                            }
                        }
                
                        // Safety check
                        if ($materialInventory->total_stocks < 0) {
                            $materialInventory->total_stocks = 0;
                        }
                
                        $materialInventory->save();
                    }
                }                
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Product Purchased Successful!',
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed. Please try again.',
            ], 500);
        }
    }
    
}
