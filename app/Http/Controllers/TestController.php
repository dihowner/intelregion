<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendorsArray = [
        
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
    
}