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
//        create table `machine_payment_record` (
//            `machine_id` int not null comment '交易設備 ID',
//            `user_id` int not null comment '交易會員 ID',
//            `transaction_amount` int not null comment '此次交易金額',
//            `transaction_type` varchar(50) not null comment '交易類型('p' => 扣款，'r' => 退款)',
//            `transaction_time` int not null comment '交易時間',
//            `note` varchar(500) null comment '交易註記'
//        ) character set utf8mb4 collate 'utf8mb4_unicode_ci'

        Schema::create('machine_payment_record', function (Blueprint $table) {
            $table->integer('machine_id')->comment('交易設備 ID')->nullable($value = false);
            $table->integer('user_id')->comment('交易會員 ID')->nullable($value = false);
            $table->integer('transaction_amount')->comment('此次交易金額')->nullable($value = false);
            $table->string('transaction_type', length: 50)->comment('交易類型')->nullable($value = false);
            $table->integer('transaction_time')->comment('交易時間')->nullable($value = false);
            $table->string('note', length: 500)->comment('交易註記')->nullable($value = true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_payment_record');
    }
};
