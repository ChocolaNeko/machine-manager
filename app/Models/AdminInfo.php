<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AdminInfo extends Authenticatable
{
    use HasApiTokens, HasFactory;
    protected $table = "admin_info";
    protected $primaryKey = "admin_id";
    protected $fillable = ["admin_name", "admin_hash", "email", "status", "create_time"];
    public $timestamps = false;
}
