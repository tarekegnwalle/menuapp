<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource (READ: All).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Fetch all categories and return them as JSON
        return response()->json(Category::all());
    }

    /**
     * Store a newly created resource in storage (CREATE).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'name' => 'required|string|unique:categories|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB Max
        ]);

        $imagePath = null;
        
        // 2. Handle Image Upload
        if ($request->hasFile('image')) {
            // Store the image in storage/app/public/categories and get the path
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        // 3. Create the Category
        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath, // Save the path to the database
        ]);

        // Return the created resource with a 201 Created status
        return response()->json($category, 201);
    }

    /**
     * Display the specified resource (READ: Single).
     *
     * @param  Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    // Note: Laravel's Route Model Binding automatically fetches the Category by ID
    public function show(Category $category) 
    {
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage (UPDATE).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        // 1. Validation (Ignore unique rule for the current record)
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        $data = $request->except('image'); // Get all data except 'image'
        $oldImagePath = $category->image; // Store old image path

        // 2. Handle Image Update/Replacement
        if ($request->hasFile('image')) {
            // Store the new image
            $data['image'] = $request->file('image')->store('categories', 'public');
            
            // Delete the old image if it existed
            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }
        } 
        // 3. Handle image removal if the frontend sends a signal (e.g., clear_image=true)
        else if ($request->boolean('clear_image')) {
            $data['image'] = null;
            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        // 4. Update the Category
        $category->update($data);

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage (DELETE).
     *
     * @param  Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        // 1. Delete the associated image from storage
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        
        // 2. Delete the record
        $category->delete();

        // Return a 204 No Content status
        return response()->json(null, 204);
    }
}