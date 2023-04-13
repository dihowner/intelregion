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
        Schema::create('wallet_in', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->string('description');
            $table->float('old_balance', 8, 2)->default(0);
            $table->float('amount', 8, 2)->default(0);
            $table->float('new_balance', 8, 2)->default(0);
            $table->string('reference')->unique();
            $table->string('external_reference')->unique()->nullable();
            $table->enum('status', ['0', '1', '2'])->default(0);
            $table->string('wallet_type')->default('wallet_in')->comment('use to different the table type');
            $table->json('remark')->nullable();
            $table->foreign('user_id')->references('id')->on("users")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_in');
    }
};