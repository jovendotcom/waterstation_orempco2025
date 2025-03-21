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

class ProductAdminController extends Controller
{
    // Display the product inventory
    public function productInventory()
    {
        $products = ProductInventory::with(['subcategory', 'materials'])->get();
        $subcategories = Subcategory::all();
        $materials = MaterialInventory::all();

        return view('admin.product_inventory_admin', [
            'products' => $products,
            'subcategories' => $subcategories,
            'materials' => $materials,
        ]);
    }

    // Store a new product
    public function storeProduct(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products_inventory', 'product_name')->where(function ($query) use ($request) {
                    return $query->where('subcategory_id', $request->subcategory_id)
                                 ->where('size_options', $request->size_options);
                }),
            ],
            'price' => 'required|numeric|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'size_options' => 'nullable|string|max:50',
            'materials' => 'nullable|array',
            'materials.*.material_id' => 'nullable|exists:materials_inventory,id',
            'materials.*.quantity_used' => 'nullable|numeric|min:0',
        ]);
    
        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
        }
    
        // Create the product
        $product = ProductInventory::create([
            'product_name' => $validated['product_name'],
            'price' => $validated['price'],
            'subcategory_id' => $validated['subcategory_id'],
            'product_image' => $imagePath,
            'size_options' => $validated['size_options'],
        ]);
    
        // Attach materials to the product if provided
        if (!empty($request->materials)) {
            foreach ($request->materials as $material) {
                if (!empty($material['material_id']) && !empty($material['quantity_used'])) {
                    $product->materials()->attach($material['material_id'], [
                        'quantity_used' => $material['quantity_used']
                    ]);
                }
            }
        }
    
        return redirect()->route('admin.productInventoryAdmin')->with('success', 'Product added successfully!');
    }

    public function edit($id)
    {
        // Fetch the product by ID
        $product = ProductInventory::findOrFail($id);

        // Fetch necessary data for dropdowns
        $subcategories = Subcategory::all();
        $materials = MaterialInventory::all();

        // Pass data to the view
        return view('admin.edit_product', [
            'product' => $product,
            'subcategories' => $subcategories,
            'materials' => $materials,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products_inventory', 'product_name')->ignore($id)->where(function ($query) use ($request) {
                    return $query->where('subcategory_id', $request->subcategory_id)
                                ->where('size_options', $request->size_options);
                }),
            ],
            'price' => 'required|numeric|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'size_options' => 'nullable|string|max:50',
            'materials' => 'nullable|array',
            'materials.*.material_id' => 'nullable|exists:materials_inventory,id',
            'materials.*.quantity_used' => 'nullable|numeric|min:0',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Fetch the product by ID
            $product = ProductInventory::findOrFail($id);

            // Handle image upload
            $imagePath = $product->product_image; // Keep the existing image by default
            if ($request->hasFile('product_image')) {
                // Delete the old image if it exists
                if ($product->product_image && Storage::exists($product->product_image)) {
                    Storage::delete($product->product_image);
                }
                // Store the new image
                $imagePath = $request->file('product_image')->store('product_images', 'public');
            }

            // Update the product
            $product->update([
                'product_name' => $validated['product_name'],
                'price' => $validated['price'],
                'subcategory_id' => $validated['subcategory_id'],
                'product_image' => $imagePath,
                'size_options' => $validated['size_options'],
            ]);

            // Sync materials (remove old materials and attach new ones)
            $product->materials()->detach(); // Remove existing materials
            if (!empty($request->materials)) {
                foreach ($request->materials as $material) {
                    if (!empty($material['material_id']) && !empty($material['quantity_used'])) {
                        $product->materials()->attach($material['material_id'], [
                            'quantity_used' => $material['quantity_used']
                        ]);
                    }
                }
            }

            // Commit the transaction
            DB::commit();

            // Redirect with success message
            return redirect()->route('admin.productInventoryAdmin')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error
            Log::error('Failed to update product: ' . $e->getMessage());

            // Redirect with error message
            return redirect()->back()->with('fail', 'Failed to update product. Please try again.');
        }
    }

    public function destroy($id)
    {
        // Start a database transaction
        DB::beginTransaction();
    
        try {
            // Find the product
            $product = ProductInventory::findOrFail($id);
    
            // Detach all materials associated with the product
            $product->materials()->detach();
    
            // Delete the product
            $product->delete();
    
            // Commit the transaction
            DB::commit();
    
            // Manually set the session flash message
            session()->flash('success', 'Product deleted successfully!');
    
            // Redirect to the product inventory page
            return redirect()->route('admin.productInventoryAdmin');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
    
            // Log the error
            Log::error('Failed to delete product: ' . $e->getMessage());
    
            // Manually set the session flash message for failure
            session()->flash('fail', 'Failed to delete product. Please try again.');
    
            // Redirect back
            return redirect()->back();
        }
    }
}
