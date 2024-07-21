<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\User;
use App\Http\Middleware\AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    echo "test";
    die();
    return $request->user();
})->middleware('auth:sanctum');

//Route::post('/login', [User::class, 'login']);

// user
Route::post('/user/newuser', [User::class, 'NewUser']);
Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {
    Route::get('/user/userinfo', [User::class, 'UserInfo']);
    Route::get('/user/payment-records', [User::class, 'PaymentRecord']);
    Route::post('/user/payment', [User::class, 'Payment']);
});
Route::post('/user/login', [User::class, 'Login']);

// admin
Route::post('/admin/newadmin', [Admin::class, 'NewAdmin']);
Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
    Route::get('/admin/userlist', [Admin::class, 'GetUserList']);
    Route::post('/admin/new-machine', [Admin::class, 'NewMachine']);
});
Route::post('/admin/login', [Admin::class, 'Login']);
