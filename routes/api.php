<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ChampionnatsController;
use App\Http\Controllers\ProductsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post("login",[AuthController::class,"login"]);
// A FAIRE ROUTE ADMIN
Route::post("add_category",[CategoriesController::class,"addCategoryToBDD"]);
Route::delete("delete_category/{id}",[CategoriesController::class,"deleteCategory"]);
Route::get("get_product",[ProductsController::class,"getProduct"]);
Route::post("test",[ProductsController::class,"test"]);
