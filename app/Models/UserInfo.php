<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class UserInfo extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = "user_info";
    protected $primaryKey = "user_id";
    protected $fillable = ["user_name", "user_hash", "email", "status", "create_time", "balance"];
    public $timestamps = false;
}
