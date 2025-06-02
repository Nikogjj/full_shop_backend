<?php

use App\Http\Controllers\ChampionnatsController;
use App\Http\Controllers\ProductsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("get_all_championnats", [ChampionnatsController::class,"getAllChampionnats"]);

// A FAIRE ROUTE ADMIN
Route::post("add_championnat",[ChampionnatsController::class,"addChampionnatToBDD"]);
Route::post("add_team_to_championnat",[ChampionnatsController::class,"addTeamToChampionnat"]);
