<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletIn extends Model
{
    use HasFactory;
    protected $table = "wallet_in";

    protected $fillable = ["user_id", "description", "amount", "reference", "status"];
    
}