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
//        create table `admin_info` (
//            `admin_id` int not null auto_increment primary key,
//            `admin_name` varchar(255) not null,
//            `admin_hash` varchar(255) not null,
//            `email` varchar(500) not null,
//            `status` int not null,
//            `create_time` int not null
//            ) default character set utf8mb4 collate 'utf8mb4_unicode_ci'
//        alter table `admin_info` add unique `admin_info_email_unique`(`email`)

        Schema::create('admin_info', function (Blueprint $table) {
            $table->integer('admin_id')->autoIncrement();
            $table->string('admin_name', length: 255)->nullable($value = false);
            $table->string('admin_hash', length: 255)->nullable($value = false);
            $table->string('email', length: 500)->nullable($value = false)->unique();
            $table->integer('status')->nullable($value = false);
            $table->integer('create_time')->nullable($value = false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_info');
    }
};
