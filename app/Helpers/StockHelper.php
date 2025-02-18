<?php

namespace App\Helpers;

use App\Models\MaterialRequirement;
use Illuminate\Support\Facades\Log;

class StockHelper
{
    /**
     * Get the required quantity of a material per product.
     *
     * @param int $productId
     * @param int $materialId
     * @return int
     */
    public static function getMaterialRequirement($productId, $materialId)
    {
        try {
            $requirement = MaterialRequirement::where('product_id', $productId)
                ->where('material_id', $materialId)
                ->first();

            return $requirement ? $requirement->quantity_needed : 1; // Default to 1 if not found
        } catch (\Exception $e) {
            Log::error("Error fetching material requirement: " . $e->getMessage());
            return 1;
        }
    }
}
