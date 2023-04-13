<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletOut extends Model
{
    use HasFactory;
    protected $table = "wallet_out";
    
    protected $fillable = ["user_id", "description", "old_balance", "amount", "new_balance", "reference", "status"];
}