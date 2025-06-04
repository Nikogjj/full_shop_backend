<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index(){
        return "ok";
    }
    public function getParentsCategories(Request $request){
        $categories = Categories::select("*")->where("parent_id",null)->get();
        $tab_categories = array();
        foreach($categories as $category){
            array_push($tab_categories,[
                "id" => $category->id,
                "name" => $category->name,
            ]);
        }
        return $tab_categories;
    }

    /**
     * Ajouter une nouvelle catégorie à la base de données.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addCategory(Request $request)
    {
        //J'attend un tab JSON avec "name" et "parent_id" (optionnel)
        try {

            $validatedRequest = $request->validate([
                '*.name' => 'required|string|max:255',
                '*.parent_id' => 'nullable|exists:categories,id',
            ]);
            $lenght = count($validatedRequest);

            $responseBDD = array();
            
            for ($i = 0; $i < $lenght; $i++) {
                // // Je crée une nouvelle catégorie a chaque boucle
                $category = Categories::create([
                    "name" => $validatedRequest[$i]["name"],
                    "parent_id"=> $validatedRequest[$i]["parent_id"] ?? null,
                ]);
                $category->save();
                array_push($responseBDD, $category);
            }
            return response()->json(['message' => 'Catégorie(s) ajoutée(s) avec succès', 'category' => $responseBDD], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erreur durant la création de la catégorie ',
            ], 500);
        }
    }

    /**
     * Supprime une catégorie de la base de données via son ID.
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function deleteCategory(string $id,Request $request)
    {
        //Je vérifie que l'id est un nombre
        if (!is_numeric($id)) {
            return response()->json([
                "message" => "L'id de la catégorie ne correspond à aucune catégorie"
            ], 400);
        }
        try {
            //Je cherche la catégorie dans la base de données
            $category = Categories::find($id);

            if ($category) {
                $category->delete();
                return response()->json([
                    "message" => "La catégorie a bien été supprimée"
                ], 201);
            }
            //Si la ctaégorie n'existe pas je retourne un message d'erreur
            else {
                return response()->json([
                    "message" => "La catégorie n'existe pas"
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la suppression de la catégorie : ",
                $th->getMessage()
            ], 500);
        }
    }
}
