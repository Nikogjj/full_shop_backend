<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ChampionnatsController;
use App\Http\Controllers\ProductsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// A FAIRE ROUTE ADMIN
Route::post("add_category",[CategoriesController::class,"addCategoryToBDD"]);
