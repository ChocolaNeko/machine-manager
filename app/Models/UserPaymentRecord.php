<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentRecord extends Model
{
    use HasFactory;
    protected $table = "user_payment_record";
//    protected $primaryKey = "user_id";
    protected $fillable = ["user_id", "machine_id", "transaction_amount", "transaction_type", "after_transaction_balance", "transaction_time", "note"];
    public $timestamps = false;
}
