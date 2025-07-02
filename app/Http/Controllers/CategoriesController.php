<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Récupère une seule catégorie en fonction de son ID
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getCategoryById(string $id){
        //Je vérifie que l'id est un nombre
        if (!is_numeric($id)) {
            return response()->json([
                "message" => "L'id de la catégorie ne correspond à aucune catégorie"
            ], 400);
        }
        //Je cherche la catégorie dans la base de données
        $category = Categories::find($id);
        if ($category) {
            $nom_parent = "";
            if ($category->parent_id != null) {
                $parent = Categories::find($category->parent_id);
                if ($parent) $nom_parent = $parent->name;
                else $nom_parent = null;
            } 
            else {
                $nom_parent = null;
            }
            return response()->json([
                "id" => $category->id,
                "nom" => $category->name,
                "nom_parent" => $nom_parent,
                "dateCreation" => $category->created_at,
                "dateModification" => date($category->updated_at)
            ], 200);
        }
        else{
            return response()->json([
                "message" => "La catégorie n'existe pas"
            ], 404);
        }
    }

    /**
     * Récupère toutes les catégories qui n'ont pas de parent (catégories principales)
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getParentsCategories(Request $request){
        $categories = Categories::select("*")->where("parent_id",null)->get();
        $tab_categories = array();
        foreach($categories as $category){
            array_push($tab_categories,[
                "id" => $category->id,
                "nom" => $category->name,
                "dateCreation" => $category->created_at,
                "dateModification" => date($category->updated_at)
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
                // Je crée une nouvelle catégorie a chaque boucle
                $category = Categories::create([
                    "name" => $validatedRequest[$i]["name"],
                    "parent_id"=> $validatedRequest[$i]["parent_id"] ?? null,
                    "nombre_de_parents" => $validatedRequest[$i]["parent_id"] 
                        ? Categories::select("nombre_de_parents")
                        ->where("id", $validatedRequest[$i]["parent_id"])
                        ->first()->nombre_de_parents + 1 : 0
                ]);

                $category->save();
                array_push($responseBDD, $category);
            }
            // return response()->json(['message' => 'Catégorie(s) ajoutée(s) avec succès', 'category' => $responseBDD], 201);
            return response()->json(['message' => 'Catégorie(s) ajoutée(s) avec succès', 'category' => $responseBDD[0]->nombre_de_parents], 201);
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

            //Je vérifie que la catégorie existe
            if (!$category) {
                return response()->json([
                    "message" => "La catégorie n'existe pas"
                ], 404);
            }

            // Appel métier via le modèle
            // $category est un instance de Categories donc je peux appeler la méthode
            // decrementChildrenRecursively() pour décrémenter le nombre de parents de ses enfants
            // sans avoir à lui passer l'id de la catégorie
            $category->decrementeEnfantRecursivement();

            // Suppression de la catégorie
            $category->delete();

            return response()->json([
                "message" => "La catégorie a bien été supprimée, et ses enfants ont été mis à jour"
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la suppression de la catégorie : ",
                $th->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour une catégorie en fonction de son ID.
     * @param string $id
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateCategory(string $id, Request $request){
        //J'attend un objet JSON avec "name" (optionnel) et "parent_id" (optionnel)
        $validatedRequest = $request->validate([
            "name" => "nullable|string|max:50|regex:/^[a-zA-Z0-9\s]+$/", // regex pour autoriser uniquement les lettres, chiffres et espaces
            "parent_id" => "nullable|integer|exists:categories,id",
        ]);
        //Je vérifie que l'id est un nombre
        if (!is_numeric($id)) {
            return response()->json([
                "message" => "L'id de la catégorie ne correspond à aucune catégorie"
            ], 400);
        }
        //Je cherche la catégorie dans la base de données
        $category = Categories::find($id);

        //Je vérifie que la catégorie existe
        if ($category) {
            if ($validatedRequest["parent_id"] != null) {
                //Je vérifie que la catégorie n'est pas son propre parent
                if ($category->CheckBoucleInfinie($validatedRequest["parent_id"])) {
                    return response()->json([
                        "message" => "La catégorie ne peut pas être son propre parent"
                    ], 400);
                }
                else{
                    //Je mets à jour le parent_id de la catégorie
                    $category->parent_id = $validatedRequest["parent_id"];
                    $category->nombre_de_parents = Categories::select("nombre_de_parents")
                        ->where("id", $validatedRequest["parent_id"])
                        ->first()->nombre_de_parents + 1;
                    
                    //J'incremente le nombre de parents des enfants de la catégorie
                    $category->incrementeNbParentRecursivement();
                }
            }
            //Je mets à jour le nom de la catégorie
            if ($validatedRequest["name"] != null) {
                //Je vérifie que le nom de la catégorie n'est pas vide
                if (empty($validatedRequest["name"])) {
                    return response()->json([
                        "message" => "Le nom de la catégorie ne peut pas être vide"
                    ], 400);
                }
                //Je mets à jour le nom de la catégorie
                $category->name = $validatedRequest["name"];
            }
            //Je sauvegarde les modifications
            $category->save();
            return response()->json([
                "message" => "La catégorie a bien été mise à jour",
                "category" => [
                    "id" => $category->id,
                    "nom" => $category->name,
                    "parent_id" => $category->parent_id,
                    "nombre_de_parents" => $category->nombre_de_parents,
                    "dateCreation" => $category->created_at,
                    "dateModification" => $category->updated_at
                ]
            ], 200);
        } 
        else {
            return response()->json([
                "message" => "La catégorie n'existe pas"
            ], 404);
        }   
    }

    /**
     * Vérifie si le nom de la catégorie existe déjà dans la base de données
     * @param string $name
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function checkIfCategoryNameExists(string $name){
        //Je vérifie que le nom de la catégorie n'est pas vide
        if (empty($name)) {
            return response()->json([
                "message" => "Le nom de la catégorie ne peut pas être vide"
            ], 400);
        }
        //Je vérifie que le nom ne contient pas un code malveillant
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $name)) {
            return response()->json([
                "message" => "Le nom de la catégorie contient des caractères interdits"
            ], 400);
        }
        //Je cherche la catégorie dans la base de données
        $category = Categories::where("name", $name)->first();
        if ($category) {
            return response()->json([
                "message" => "La catégorie existe déjà"
            ], 404);
        } else {
            return response()->json([
                "message" => "La catégorie n'existe pas"
            ], status: 200);
        }
    }
}
