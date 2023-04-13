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
        Schema::create('apis', function (Blueprint $table) {
            $table->id();
            $table->string("api_name");
            $table->unsignedBigInteger("api_vendor_id");
            $table->string("api_username")->nullable();
            $table->string("api_password")->nullable();
            $table->string("api_private_key")->nullable();
            $table->string("api_secret_key")->nullable();
            $table->enum("api_delivery_route", ["instant", "cron"])->default('instant');
            $table->foreign('api_vendor_id')->references('id')->on("vendors")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apis');
    }
};