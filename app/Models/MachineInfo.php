<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class MachineInfo extends Authenticatable
{
    use HasApiTokens;
    protected $table = "machine_info";
    protected $primaryKey = "machine_id ";
    protected $fillable = ["machine_name", "status", "create_time", "update_time"];
    public $timestamps = false;
}
