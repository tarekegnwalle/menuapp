<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;


use App\Http\Controllers\AuthController;
// 📢 Ensure this line is present and correct
use App\Http\Controllers\CategoryController; 
use App\Http\Controllers\MenuItemController; 

// --- PUBLIC ROUTES (No Authentication) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- PROTECTED ROUTES (Requires auth:sanctum Middleware) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Category Management CRUD Routes
    Route::apiResource('categories', CategoryController::class); 

    // Menu Item Management CRUD Routes
    Route::apiResource('menu-items', MenuItemController::class); 
    // Other authenticated routes...
    Route::get('/qr-code', [QrCodeController::class, 'generate']);
});