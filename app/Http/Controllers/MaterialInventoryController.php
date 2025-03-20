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

class MaterialInventoryController extends Controller
{
    public function getMaterialInventory()
    {
        $materials = MaterialInventory::with('category')->get();
        $categories = Category::all();
        $unitsOfMeasurement = [
            'grams' => 'Grams',
            'ml' => 'Milliliters',
            'pieces' => 'Pieces',
        ];

        return view('admin.material_inventory', [
            'materials' => $materials,
            'categories' => $categories,
            'unitsOfMeasurement' => $unitsOfMeasurement
        ]);        
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'material_name' => [
                'required', 'string', 'max:255',
                Rule::unique('materials_inventory', 'material_name')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                })
            ],
            'unit_of_measurement' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'total_stocks' => 'required|integer|min:0',
            'low_stock_limit' => 'nullable|integer|min:0', // Add validation for low stock limit
        ]);
    
        MaterialInventory::create([
            'category_id'     => $validated['category_id'],
            'material_name'   => $validated['material_name'],
            'unit'            => $validated['unit_of_measurement'],
            'cost_per_unit'   => $validated['cost_per_unit'],
            'total_stocks'    => $validated['total_stocks'],
            'low_stock_limit' => $validated['low_stock_limit'], // Add low stock limit
        ]);
    
        return redirect()->back()->with('success', 'New material added successfully!');
    }

    // Edit material
    public function edit($id)
    {
        $material = MaterialInventory::findOrFail($id);
        $categories = Category::all();
        $unitsOfMeasurement = [
            'grams' => 'Grams',
            'ml' => 'Milliliters',
            'pieces' => 'Pieces',
        ];

        return view('admin.edit_material', [
            'material' => $material,
            'categories' => $categories,
            'unitsOfMeasurement' => $unitsOfMeasurement
        ]);
    }

    // Update material
    public function update(Request $request, $id)
    {
        $material = MaterialInventory::findOrFail($id);
    
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'material_name' => [
                'required', 'string', 'max:255',
                Rule::unique('materials_inventory', 'material_name')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                })->ignore($material->id)
            ],
            'unit_of_measurement' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'total_stocks' => 'required|integer|min:0',
            'low_stock_limit' => 'nullable|integer|min:0', // Add validation for low stock limit
        ]);
    
        $material->update([
            'category_id'     => $validated['category_id'],
            'material_name'   => $validated['material_name'],
            'unit'            => $validated['unit_of_measurement'],
            'cost_per_unit'   => $validated['cost_per_unit'],
            'total_stocks'    => $validated['total_stocks'],
            'low_stock_limit' => $validated['low_stock_limit'], // Add low stock limit
        ]);
    
        return redirect()->route('admin.materialInventory')->with('success', 'Material updated successfully!');
    }

    // Delete material
    public function destroy($id)
    {
        $material = MaterialInventory::findOrFail($id);
        $material->delete();

        return redirect()->back()->with('success', 'Material deleted successfully!');
    }
    
}
