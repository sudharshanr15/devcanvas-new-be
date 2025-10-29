<?php

use App\Http\Controllers\DatabaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Session\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix("auth")->group(function(){
    Route::prefix("user")->group(function(){
        Route::post("/login", [UserController::class, "store"]);
        Route::post('/logout', [UserController::class, "destroy"]);
    });
});

Route::middleware("auth")->group(function(){
    Route::get("/databases", [DatabaseController::class, "show"]);
    Route::post("/databases", [DatabaseController::class, "store"]);
});