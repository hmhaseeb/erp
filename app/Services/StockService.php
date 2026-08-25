<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Exception;

class StockService
{
    /**
     * Record a stock movement and update product stock & weighted average cost.
     */
    public function recordMovement(
        int $productId,
        string $date,
        string $movementType,
        float $quantityIn,
        float $quantityOut,
        float $unitCost,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        $product = Product::findOrFail($productId);

        $currentQty = (float) $product->current_stock;
        $currentCost = (float) $product->weighted_cost;

        $newQty = $currentQty + $quantityIn - $quantityOut;

        if ($newQty < 0 && $movementType === 'SALE') {
            $generalSetting = \App\Models\GeneralSetting::first();
            $allowNegative = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;
            if (!$allowNegative) {
                throw new Exception("Insufficient stock for product: {$product->name}. Current stock: {$currentQty}, requested: {$quantityOut}");
            }
        }

        // Weighted Average Cost calculation on Purchase / Adjustment In
        $newCost = $currentCost;
        if ($quantityIn > 0 && ($movementType === 'PURCHASE' || $movementType === 'OPENING' || $movementType === 'ADJUSTMENT_IN')) {
            $totalCurrentValuation = $currentQty * $currentCost;
            $totalNewIncomingValuation = $quantityIn * $unitCost;
            $totalNewQuantity = $currentQty + $quantityIn;

            if ($totalNewQuantity > 0) {
                $newCost = ($totalCurrentValuation + $totalNewIncomingValuation) / $totalNewQuantity;
            } else {
                $newCost = $unitCost;
            }
        }

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'date' => $date,
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'unit_cost' => $unitCost,
            'balance_quantity' => $newQty,
            'notes' => $notes,
        ]);

        $product->update([
            'current_stock' => $newQty,
            'weighted_cost' => round($newCost, 4),
        ]);

        return $movement;
    }
}
