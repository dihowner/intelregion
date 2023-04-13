<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string("vendor_name");
            $table->string("vendor_code");
            $table->string("vendor_requirement");
        });
        
        $vendorsArray = [
            [
                "Not Available",
                "not_available",
                ""
            ],
            [
                "Local Server",
                "local_server",
                "username"
            ],
            [
                "Mobile Nig",
                "mobilenig",
                "username,password"
            ]
        ];
        $this->createVendors($vendorsArray);
    }
    
    private function createVendors($vendorsArray){
        foreach($vendorsArray as $index => $vendor) {
            DB::table("vendors")->insert([
                "vendor_name" => $vendor[0],
                "vendor_code" => $vendor[1],
                "vendor_requirement" => $vendor[2]
            ]);
        }
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};