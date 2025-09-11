<?php

use App\Http\Controllers\DatabaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix("/databases")->group(function(){
    Route::get("/", [DatabaseController::class, "index"])->name("database.index");
    Route::post("/", [DatabaseController::class, "store"]);
});