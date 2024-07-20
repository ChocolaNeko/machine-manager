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
//        create table `machine_info` (
//          `machine_id` int not null auto_increment primary key,
//          `machine_name` varchar(255) not null,
//          `status` int not null,
//          `create_time` int not null,
//          `update_time` int not null
//        ) default character set utf8mb4 collate 'utf8mb4_unicode_ci'

        Schema::create('machine_info', function (Blueprint $table) {
            $table->integer('machine_id')->autoIncrement();
            $table->string('machine_name', length: 255)->nullable($value = false);
            $table->integer('status')->nullable($value = false);
            $table->integer('create_time')->nullable($value = false);
            $table->integer('update_time')->nullable($value = false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_info');
    }
};
