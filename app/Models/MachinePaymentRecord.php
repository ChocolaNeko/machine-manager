<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachinePaymentRecord extends Model
{
    use HasFactory;
    protected $table = "machine_payment_record";
//    protected $primaryKey = "user_id";
    protected $fillable = ["machine_id", "user_id", "transaction_amount", "transaction_type", "transaction_time", "note"];
    public $timestamps = false;
}
