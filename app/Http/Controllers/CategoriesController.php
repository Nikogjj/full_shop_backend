<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Ajouter une nouvelle catégorie à la base de données.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addCategoryToBDD(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
            ]);
    
            // Je crée une nouvelle catégorie
            $category = Categories::create([
                "name" => $request->name,
                "parent_id"=> $request->parent_id,
            ]);
            $category->save();
            return response()->json(['message' => 'Category added successfully', 'category' => $category], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erreur durant la création de la catégorie :' . $th->getMessage(),
            ], 500);
        }
    }
}
