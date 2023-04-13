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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("content");
            $table->timestamps();
        });

        Schema::create('virtual_banks', function (Blueprint $table) {
            $table->id();
            $table->string("bank_code")->unique();
            $table->string("bank_name")->unique();
            $table->timestamp('date_created');
        });

        // Insert some stuff
        $settingsArray = [
            'default_plan_id' => '1',
            'monnify' => '{"baseUrl":"https:\/\/sandbox.monnify.com","apiKey":"MK_TEST_SXSY8BH2T8","secKey":"GA84CM4UA9Z4S38GTZ3YMMES96NBBF3S","contractCode":"1378644451","chargestype":"percentage","charges":"51.45","percent":0,"deposit_amount":"10000"}'
        ];
        $this->loadSettings($settingsArray);

        // Insert some stuff
        $bankArray = [
            'Wema Bank' => '035',
            'Sterling Bank' => '232'
        ];

        $this->loadBanks($bankArray);

    }
    
    private function loadBanks($bankArray) {
        foreach($bankArray as $index => $bank) {
            DB::table('virtual_banks')->insert(['bank_code' => $bank, 'bank_name' => $index]);
        }
    }
    
    private function loadSettings($settingsArray) {
        foreach($settingsArray as $name => $content) {
            DB::table('settings')->insert(['content' => $content, 'name' => $name]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
