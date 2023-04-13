<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create categories table 
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string("category_name");
        });
        
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("product_name");
            $table->string("product_id");
            $table->enum("catgory", ['Airtime Topup', 'Data Bundle', 'Cable TV', 'Electricity Bills', 'Educational Bills'])->default('Airtime Topup');
            $table->float("cost_price", 8, 2)->default(0);
            $table->unsignedBigInteger("api_key")->default(0);
        });

        $this->insertCategory();
    }

    // Insert into categories table 
    private function insertCategory() {
        $categories = ['Airtime Topup', 'Data Bundle', 'Cable TV', 'Electricity Bills', 'Educational Bills'];
        foreach($categories as $index => $category) {
            DB::table('categories')->insert(["category_name" => $category]);
        }
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};