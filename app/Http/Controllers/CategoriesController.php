<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Log;
use Ramsey\Uuid\Type\Integer;

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
        // Si la catégorie existe je la return
        if ($category) {
            return response()->json([
                "message" => "La catégorie a bien été trouvée",
                "categorie" => $category
            ], 200);
        }
        // Si la catégorie n'existe pas je return un message d'erreur
        else{
            return response()->json([
                "message" => "La catégorie n'existe pas",
                "categorie" => ""
            ], 404);
        }
    }

    /**
     * Récupère toutes les catégories de la base de données
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAllCategories(Request $request){
        //Je récupère toutes les catégories de la base de données
        try {
            $categories = Categories::select("*")->get();
            return response()->json([
                "message" => "Toutes les catégories ont été récupérées avec succès",
                "categories" => $categories
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la récupération des catégories : " . $th->getMessage(),
                "categories" => []
            ], 500);
        }
    }

    /**
     * Récupère les catégories par page
     * @param int $page
     * @param \Illuminate\Http\Request $index
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getCategoriesByPage(int $page){
        //Je vérifie que la page est un nombre
        if (!is_numeric($page) || $page < 1) {
            return response()->json([
                "message" => "La page doit être un nombre positif",
                "categories" => []
            ], 400);
        }
        //Je récupère les catégories de la base de données
        try {
            // On récupère une liste paginée de catégories depuis le modèle Categories
            // La méthode paginate permet de diviser les résultats en pages
            // - Le premier argument (10) indique le nombre d'éléments par page
            // - Le deuxième argument (["*"]) indique les colonnes à sélectionner
            // - Le troisième argument ("page") est le nom du paramètre de pagination dans l'URL
            // - Le quatrième argument ($page) est la valeur actuelle de la page qu'on a récupérée dans les paramateres
            // La méthode items extrait uniquement les éléments de la page courante car j'ai besoin que de ca sur mon frontend
            $categories = Categories::paginate(10, ["*"], "page", $page)->items();
            return response()->json([
            "message" => "Les catégories ont été récupérées avec succès",
            "categories" => $categories
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la récupération des catégories : " . $th->getMessage(),
                "categories" => []
            ], 500);
        }
    }

    /**
     * Ajouter une nouvelle catégorie à la base de données.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addCategory(Request $request)
    {
        try {
            // Je vérifie que le nom de la catégorie n'est pas vide et qu'il ne contient pas de caractères interdits
            $validatedRequest = $request->validate([
                "name" => "required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/", // regex pour autoriser lettres, chiffres et espaces
            ]);
            // Je crée une nouvelle catégorie
            $categorie = Categories::create([
                "name" => $validatedRequest["name"],
            ]);
            // Je sauvegarde la catégorie dans la base de données
            $categorie->save();

            return response()->json([
                "message" => "Catégorie ajoutée avec succès", 
                "categorie" => "test", 201]);
        }
        catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la création de la catégorie",
                "categorie" => "",
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
                "message" => "L'id de la catégorie ne correspond à aucune catégorie",
                "id" => $id
            ], 400);
        }
        try {
            //Je cherche la catégorie dans la base de données
            $category = Categories::find($id);

            //Je vérifie que la catégorie existe
            if (!$category) {
                return response()->json([
                    "message" => "La catégorie n'existe pas",
                    "id" => $id
                ], 404);
            }

            // Suppression de la catégorie
            $category->delete();

            return response()->json([
                "message" => "La catégorie a bien été supprimée",
                "id" => $id
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la suppression de la catégorie : ",
                "id" => $id,
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
        // Validation des données reçues
        try {
            $validatedRequest = $request->validate([
                "name" => "nullable|string|max:50|regex:/^[a-zA-Z0-9\s]+$/", // Autorise lettres, chiffres et espaces
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la validation des données : " . $th->getMessage(),
                "categorie" => ""
            ], 400);
        }

        // Vérification que l'id est un nombre
        if (!is_numeric($id)) {
            return response()->json([
                "message" => "L'id de la catégorie ne correspond à aucune catégorie",
                "categorie" => ""
            ], 400);
        }
        // Vérifie que le nom de la catégorie n'existe pas déjà et qu'il ne correspond pas à l'id de la catégorie en cours de modification
        if ($validatedRequest["name"] != null) {
            $existingCategory = Categories::where("name", $validatedRequest["name"])->first();
            if ($existingCategory && $existingCategory->id != $id) {
                return response()->json([
                    "message" => "Le nouveau nom de la catégorie existe déjà",
                    "categorie" => ""
                ], 400);
            }
        }

        // Recherche de la catégorie dans la base de données
        $category = Categories::find($id);

        // Vérification que la catégorie existe
        if (!$category) {
            return response()->json([
                "message" => "La catégorie n'existe pas",
                "categorie" => ""
            ], 404);
        }
        // Mise  a jour du nom
        if ($validatedRequest["name"]) {
            $category->name = $validatedRequest["name"];
        }

        // Sauvegarde des modifications
        $category->save();

        return response()->json([
            "message" => "La catégorie a bien été mise à jour",
            "categorie" => $category
        ], 200);
    }

    /**
     * Vérifie si le nom de la catégorie existe déjà dans la base de données
     * @param string $name
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function checkIfCategoryNameExists(string $name){
        try {
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
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la vérification de l'existence du nom de la catégorie :". $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les catégories en fonction de leur nom
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getCategoriesByName(string $name){
        //Je vérifie que le nom de la catégorie n'est pas vide
        if (empty($name)) {
            return response()->json([
                "message" => "Le nom de la catégorie ne peut pas être vide",
                "categories" => []
            ], 400);
        }
        //Je vérifie que le nom ne contient pas un code malveillant
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $name)) {
            return response()->json([
                "message" => "Le nom de la catégorie contient des caractères interdits",
                "categories" => []
            ], 400);
        }
        //Je cherche les catégories dans la base de données
        $categories = Categories::where("name", "LIKE", "%$name%")->get();
        if ($categories->isEmpty()) {
            return response()->json([
                "message" => "Aucune catégorie trouvée avec ce nom",
                "categories" => []
            ], 404);
        } else {
            return response()->json([
                "message" => "Catégories trouvées avec succès",
                "categories" => $categories
            ], 200);
        }
    }

    public function count (Request $request) {
        try {
            $count = Categories::count();
            return response()->json([
                "message" => "Le nombre de catégories a été récupéré avec succès",
                "count" => $count
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la récupération du nombre de catégories : " . $th->getMessage(),
                "count" => 0
            ], 500);
        }
    }
}
