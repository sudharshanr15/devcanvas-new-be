<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\Helpers\DocumentController;
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
    Route::post("/databases/{database_id}/delete", [DatabaseController::class, "destroy"]);

    Route::get("/databases/{database_id}/collections", [CollectionController::class, "show"]);
    Route::post("/databases/{database_id}/collections", [CollectionController::class, "store"]);

    Route::get("/databases/{database_id}/collections/{collection_id}/schema", [CollectionController::class, "index"]);
    Route::post("/databases/{database_id}/collections/{collection_id}/delete", [CollectionController::class, "destroy"]);
});

Route::prefix("v1")->group(function(): void{
    Route::get("/databases/{database_id}/collections/{collection_id}/documents", [DocumentController::class, "index"]);
    Route::post("/databases/{database_id}/collections/{collection_id}/documents", [DocumentController::class, "store"]);
    Route::put("/databases/{database_id}/collections/{collection_id}/documents/{document_id}", [DocumentController::class, "update"]);
    Route::delete("/databases/{database_id}/collections/{collection_id}/documents/{document_id}", [DocumentController::class, "destroy"]);
});