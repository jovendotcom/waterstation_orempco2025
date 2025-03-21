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
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'materials' => 'required|array',
            'materials.*.material_id' => 'required|exists:materials_inventory,id',
            'materials.*.quantity_used' => 'required|numeric|min:0',
        ]);
    
        // Start a database transaction
        DB::beginTransaction();
    
        try {
            // Create the product
            $product = ProductInventory::create([
                'product_name' => $validated['product_name'],
                'price' => $validated['price'],
                'subcategory_id' => $validated['subcategory_id'],
            ]);
    
            // Attach materials to the product
            foreach ($request->materials as $material) {
                $product->materials()->attach($material['material_id'], [
                    'quantity_used' => $material['quantity_used']
                ]);
            }
    
            // Commit the transaction
            DB::commit();
    
            // Log success
            Log::info('Product added successfully:', ['product' => $product]);
    
            // Set success message
            return redirect()->route('admin.productInventoryAdmin')->with('success', 'Product added successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
    
            // Log the error
            Log::error('Failed to add product: ' . $e->getMessage());
    
            // Set fail message
            return redirect()->back()->with('fail', 'Failed to add product. Please try again.');
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
