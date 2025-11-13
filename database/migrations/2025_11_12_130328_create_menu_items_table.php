// database/migrations/2025_11_12_130328_create_menu_items_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Add this line

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('price', 10, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0.00); 
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->string('photo')->nullable();

            $table->decimal('final_price', 10, 2)->stored(); 
            $table->index('category_id'); 
            
            $table->timestamps();
        });

        // 📢 FIX: Manually adding check constraints using DB::statement
        // This is necessary if your Laravel version doesn't support $table->check()
        DB::statement('ALTER TABLE menu_items ADD CONSTRAINT chk_price CHECK (price >= 0);');
        DB::statement('ALTER TABLE menu_items ADD CONSTRAINT chk_tax CHECK (tax_percentage >= 0 AND tax_percentage <= 100);');
        DB::statement('ALTER TABLE menu_items ADD CONSTRAINT chk_discount CHECK (discount >= 0 AND discount <= price);');
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};