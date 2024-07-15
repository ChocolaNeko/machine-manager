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
        // create table `user_info` (
        //     `user_id` int not null auto_increment primary key,
        //     `user_name` varchar(255) not null,
        //     `user_hash` varchar(255) not null,
        //     `email` varchar(500) not null,
        //     `status` int not null,
        //     `create_time` int not null,
        //     `money` int not null default '0'
        //     ) default character set utf8mb4 collate 'utf8mb4_unicode_ci'

        Schema::create('user_info', function (Blueprint $table) {
            $table->integer('user_id')->autoIncrement();
            $table->string('user_name', length: 255)->nullable($value = false);
            $table->string('user_hash', length: 255)->nullable($value = false);
            $table->string('email', length: 500)->nullable($value = false)->unique();
            $table->integer('status')->nullable($value = false);
            $table->integer('create_time')->nullable($value = false);
            $table->integer('balance')->nullable($value = false)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_info');
    }
};
