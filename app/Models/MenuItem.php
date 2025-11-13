<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Import the Category model
use App\Models\Category; 

class MenuItem extends Model
{
    use HasFactory;

    // 📢 1. Define the fields that can be mass assigned (CRUD operations)
    protected $fillable = [
        'category_id',
        'item_name',
        'price',
        'tax_percentage',
        'discount',
        'photo', 
        'final_price', // <-- 🟢 FIX: Added final_price to mass assignable fields
    ];
    
    // 📢 2. Define the relationship to the Category model
    // A Menu Item belongs to one Category.
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    // 📢 3. Casting the generated column for correct data type
    protected $casts = [
        'price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];
}