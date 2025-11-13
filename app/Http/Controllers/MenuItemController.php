<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MenuItemController extends Controller
{
    /**
     * Calculates the final price of the menu item.
     */
    private function calculateFinalPrice(array $data): float
    {
        // Use provided values or default to 0 if key is missing
        $price = $data['price'] ?? 0;
        $discount = $data['discount'] ?? 0;
        $tax_percentage = $data['tax_percentage'] ?? 0;

        // Formula: (Price - Discount) * (1 + Tax Rate)
        // Use max(0, ...) to ensure the sub-total is never negative
        $sub_total = max(0, $price - $discount); 
        $final_price = $sub_total * (1 + ($tax_percentage / 100));
        
        // Round to 2 decimal places for currency accuracy
        return round($final_price, 2);
    }

    /**
     * Display a listing of the menu items.
     * Includes grouping requirement (by category).
     */
    public function index()
    {
        // Eager load the 'category' relationship for grouping and displaying category name
        return MenuItem::with('category')
            ->orderBy('category_id')
            ->orderBy('item_name')
            ->get();
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
       $validatedData = $request->validate([
        'category_id' => ['required', 'exists:categories,id'],
        'item_name' => ['required', 'string', 'max:255'],
        'price' => ['required', 'numeric', 'min:0'], 
        'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        
        // TEMPORARILY REMOVE 'lt:price' to see if the crash is gone
        'discount' => ['required', 'numeric', 'min:0'], 
        
        'photo' => ['nullable', 'image', 'max:2048'], 
    ]);
        // FIX 2: Calculate final_price and add it to the data array
        $validatedData['final_price'] = $this->calculateFinalPrice($validatedData);

        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('photos/menu_items', 'public');
        }

        $item = MenuItem::create($validatedData);

        return response()->json($item->load('category'), 201);
    }

    /**
     * Update the specified menu item in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validatedData = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'item_name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'tax_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            // FIX 3: Added 'lt:price' for constraint when updating
            'discount' => ['sometimes', 'numeric', 'min:0', 'lt:price'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);
        
        // --- FIX 4: Recalculate final_price with current values + updated values ---
        
        // 1. Get the current item's attributes (will have existing price, tax, discount)
        $combinedData = $menuItem->toArray(); 
        
        // 2. Overwrite with any new validated data from the request
        $combinedData = array_merge($combinedData, $validatedData);

        // 3. Calculate and set the new final_price
        $validatedData['final_price'] = $this->calculateFinalPrice($combinedData);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($menuItem->photo) {
                Storage::disk('public')->delete($menuItem->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photos/menu_items', 'public');
        } else if ($request->input('photo') === 'null' && $menuItem->photo) {
             // Handle case where user clears the photo field
            Storage::disk('public')->delete($menuItem->photo);
            $validatedData['photo'] = null;
        }


        $menuItem->update($validatedData);

        return response()->json($menuItem->load('category'));
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        // Delete the associated photo file if it exists
        if ($menuItem->photo) {
            Storage::disk('public')->delete($menuItem->photo);
        }

        $menuItem->delete();

        return response()->json(null, 204);
    }
}