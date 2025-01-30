<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\UserLog;
use Illuminate\Validation\Rule;
use Session;

class ProductInventoryController extends Controller
{
    public function productInventory(Request $request)
    {
        $products = Product::paginate(10);
        return view('sales.product_inventory', compact('products'));
        
    }

    public function storeProduct(Request $request)
    {
        $validatedData = $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'variant' => 'nullable|string|max:255',
            'unit' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_limit' => 'required|integer|min:0',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
        }
    
        Product::create([
            'product_name' => $validatedData['product_name'],
            'category' => $validatedData['category'],
            'variant' => $validatedData['variant'],
            'unit' => $validatedData['unit'],
            'price' => $validatedData['price'],
            'quantity' => $validatedData['quantity'],
            'low_stock_limit' => $validatedData['low_stock_limit'],
            'status' => 'Available',
            'product_image' => $imagePath,
        ]);
    
        return redirect()->back()->with('success', 'Product added successfully.');
    }    
    

}
