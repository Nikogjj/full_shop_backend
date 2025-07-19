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
Route::get("get_categories_by_page/{page}",[CategoriesController::class,"getCategoriesByPage"]);
Route::get("get_all_categories",[CategoriesController::class,"getAllCategories"]);
Route::get("get_category/{id}",[CategoriesController::class,"getCategoryById"]);
Route::put("update_category/{id}",[CategoriesController::class,"updateCategory"]);
Route::delete("delete_category/{id}",[CategoriesController::class,"deleteCategory"]);
Route::get("check_if_category_name_exists/{name}",[CategoriesController::class,"checkIfCategoryNameExists"]);
Route::get("get_categories_by_name/{name}",[CategoriesController::class,"getCategoriesByName"]);
Route::get("categories/count", [CategoriesController::class, "count"]);


// Route::get("get_product",[ProductsController::class,"getProduct"]);
Route::get("products/count", [ProductsController::class, "count"]);
Route::get("get_products_by_page/{page}",[ProductsController::class,"getProductsByPage"]);
Route::get("get_product_by_id/{id}",[ProductsController::class,"getProductById"]);
Route::post("update_product/{id}",[ProductsController::class,"updateProduct"]);
Route::get("check_if_product_name_exists/{name}",[ProductsController::class,"checkIfProductNameExists"]);
Route::post("get_products",[ProductsController::class,"getProducts"]);
Route::post("get_products_by_categories",[ProductsController::class,""]);
Route::post("add_product",[ProductsController::class,"addProduct"]);
Route::delete("delete_product/{id}",[ProductsController::class,"deleteProduct"]);
Route::get("get_all_products",[ProductsController::class,"getAllProducts"]);

Route::post("add_images",[ImagesController::class,"uploadMultipleImagesInPublicStorageAndBDD"]);
// Route::delete("delete_image/{id}",[ImagesController::class,"deleteImageInPublicStorageAndBDD"]);


Route::post("test/",[ImagesController::class,"testDeleteImageInPublicStorageAndBDD"]);