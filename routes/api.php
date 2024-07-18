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
    Route::post('/user/getuserinfo', [User::class, 'GetUserInfo']);
});
Route::post('/user/login', [User::class, 'Login']);

// admin
Route::post('/admin/newadmin', [Admin::class, 'NewAdmin']);
Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
    Route::get('/admin/getuserlist', [Admin::class, 'GetUserList']);
});
Route::post('/admin/login', [Admin::class, 'Login']);

//Route::get('/getuserlist', [User::class, 'getlist'])->middleware(AuthUser::api);

//Route::get('/getuserlist', [User::class, 'getlist'])->middleware(AuthUser::class);
