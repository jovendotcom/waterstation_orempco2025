<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductForSale;
use App\Models\StocksCount;
use App\Models\UserLog;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StocksCountExport;
use PDF;
use Session;
use App\Models\Category;
use App\Models\SubCategory;

class ProductInventoryController extends Controller
{
    public function productInventory(Request $request)
    {
        $products = ProductForSale::all();
        $stocks = StocksCount::all(); // Fetch available stock items
        $subcategories = Subcategory::all(); // Fetch all subcategories
        $sizeOptions = ['Small', 'Medium', 'Large', 'Solo', 'Jumbo']; // Example size options
        $unitsOfMeasurement = ['kg', 'g', 'L', 'mL', 'pcs']; // Example units of measurement
        $categories = Category::with('stockCounts')->get();
    
        return view('sales.product_inventory', compact('products', 'stocks', 'subcategories', 'sizeOptions', 'unitsOfMeasurement', 'categories'));
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
        return view('sales.stocks_count', compact('stocks', 'categories', 'unitsOfMeasurement'));
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
    
        return redirect()->route('sales.stocksCount')->with('success', 'Stock added successfully!');
    }

    
    public function export($format)
    {
        $categories = Category::with('stockCounts')->get();
    
        if ($categories->isEmpty() || $categories->every(fn($category) => $category->stockCounts->isEmpty())) {
            return redirect()->back()->with('fail', 'No stock data found to export.');
        }
    
        if ($format === 'excel') {
            return Excel::download(new StocksCountExport, 'physical_inventory_count_form.xlsx');
        } elseif ($format === 'pdf') {
            $pdf = \PDF::loadView('exports.stocks_count', compact('categories'))
                ->setPaper('legal', 'portrait');
            return $pdf->download('physical_inventory_count_form.pdf');
        } else {
            return redirect()->back()->with('fail', 'Invalid export format selected.');
        }
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

            return redirect()->route('sales.stocksCount')->with('success', 'Stock updated successfully!');
        }

        return redirect()->route('sales.stocksCount')->with('fail', 'Stock not found!');
    }


    
    

}
