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
        Schema::create('wallet_out', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->string('description');
            $table->float('old_balance', 8, 2)->default(0);
            $table->float('amount', 8, 2)->default(0);
            $table->float('new_balance', 8, 2)->default(0);
            $table->enum('status', ['0', '1', '2'])->default(0);
            $table->json('remark')->nullable();
            $table->string('reference');
            $table->string('wallet_type')->default('wallet_out')->comment('use to different the table type');
            $table->foreign('user_id')->references('id')->on("users")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_out');
    }
};