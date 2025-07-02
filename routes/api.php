<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ChampionnatsController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\ProductsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post("login",[AuthController::class,"login"]);
// A FAIRE ROUTE ADMIN
Route::post("add_category",[CategoriesController::class,"addCategory"]);
route::get("get_parents_categories",[CategoriesController::class,"getParentsCategories"]);
Route::get("get_categories",[CategoriesController::class,"getParentsCategories"]);
Route::get("get_category/{id}",[CategoriesController::class,"getCategoryById"]);
Route::put("update_category/{id}",[CategoriesController::class,"updateCategory"]);
Route::delete("delete_category/{id}",[CategoriesController::class,"deleteCategory"]);
Route::get("check_if_category_name_exists/{name}",[CategoriesController::class,"checkIfCategoryNameExists"]);

// Route::get("get_product",[ProductsController::class,"getProduct"]);
Route::post("get_products",[ProductsController::class,"getProducts"]);
Route::post("get_products_by_categories",[ProductsController::class,""]);
Route::post("add_products",[ProductsController::class,"addProducts"]);
Route::delete("delete_product/{id}",[ProductsController::class,"deleteProduct"]);

Route::post("add_image",[ImagesController::class,"uploadImageInPublicStorageAndBDD"]);
Route::delete("delete_image/{id}",[ImagesController::class,"deleteImageInPublicStorageAndBDD"]);
