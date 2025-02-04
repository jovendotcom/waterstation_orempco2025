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
use Session;

class ProductInventoryController extends Controller
{
    public function productInventory(Request $request)
    {
        $products = ProductForSale::all();
        $stocks = StocksCount::all(); // Fetch available stock items
    
        return view('sales.product_inventory', compact('products', 'stocks'));
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
        return view('sales.stocks_count', compact('stocks'));
    }

    public function storeStockCount(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        StocksCount::create([
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('sales.stocksCount')->with('success', 'Stock added successfully!');
    }

    // Update stock count
    public function updateStockCount(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        $stock = StocksCount::where('item_name', $request->item_name)->first();
        if ($stock) {
            $stock->quantity += $request->quantity;  // Adding to the current stock
            $stock->save();

            return redirect()->route('sales.stocksCount')->with('success', 'Stock updated successfully!');
        }

        return redirect()->route('sales.stocksCount')->with('fail', 'Stock not found!');
    }


    
    

}
