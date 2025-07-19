<?php

namespace App\Http\Controllers;

use App\Models\Images;
use App\Models\Products;
use App\Models\ProductsCategories;
use App\Models\ProductsImages;
use File;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use Laravel\Pail\ValueObjects\Origin\Console;
use Log;
use Storage;

class ProductsController extends Controller
{
    /**
     * Récupère plusieurs produits à partir d'un tableau JSON d'ID de produits dans le body.
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function getProducts(request $request){
        //J'attend un tab JSON d'objet avec l'id du produit
        try {
            $validatedRequest = $request->validate([
                '*.product_id' => 'required|int',
            ]);
            $lenght = count($validatedRequest);

            $products = array();
            for ($i=0; $i < $lenght; $i++) { 
                $product = Products::find($validatedRequest[$i]["product_id"]);
                array_push($products, $product);
            }
            return response()->json([
                "message"=> "Produits trouvés",
                "products" => $products
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=> "Erreur durant la recherche des produits",
                "products" => "error"
            ]);
        }
    }

    /**
     * Recupere tout les produits d'une page ciblé
     * @param int $page
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getProductsByPage(int $page)
{
    // Je vérifie que la page est un nombre
    if (!is_numeric($page) || $page < 1) {
        return response()->json([
            "message" => "La page doit être un nombre positif",
            "produits" => []
        ], 400);
    }

    try {
        // On récupère une liste paginée de produits depuis le modèle Products
        // paginate(10) signifie 10 produits par page
        // ["*"] signifie qu'on récupère toutes les colonnes
        // "page" est le nom du paramètre de pagination
        // $page est la page demandée
        // items() extrait uniquement les éléments de la page demandée
        $produits = Products::paginate(10, ["*"], "page", $page)->items();

        return response()->json([
            "message" => "Les produits ont été récupérés avec succès",
            "produits" => $produits
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            "message" => "Erreur durant la récupération des produits : " . $th->getMessage(),
            "produits" => []
        ], 500);
    }
}

    /**
     * Ajoute un ou plusieurs produits dans la base de données a partir d'un tableau JSON de produits dans le body de la requête
     * @param \Illuminate\Http\Request $request
     * @return array|mixed|\Illuminate\Http\JsonResponse
     */
    public function addProduct(Request $request){
        // J'attend un objet JSON avec "name", "description", "price", "category_id" et "image" (optionnel)
        try {
            $validatedRequest = $request->validate([
                "nom" => "required|string|max:255",
                "description" => "nullable|string|max:1000",
                "prix" => "required|numeric|min:0",
                "categories" => "required|string", // JSON.stringify côté frontend
                // "images.*" => "nullable|file|image|max:5000", // fichiers uploadés
            ]);


            $nom = $validatedRequest["nom"];
            $description = $validatedRequest["description"];
            $prix = $validatedRequest["prix"];
            // Je vérifie que la string categories est un JSON valide
            $categories = json_decode($validatedRequest["categories"], true);
            // si il y a une erreur avec la fonction json_decode je return une erreur
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    "message" => "La chaîne de caractères des catégories n'est pas un JSON valide",
                    "product" => "error"
                ], 400);
            }
            // $nbCategories = count($categories);

            // Je crée mon produit dans la base de données
            $produit = Products::create([
                "name" => $nom,
                "description" => $description,
                "price" => $prix,
            ]);

            // je fai sun foreach sur le tableau de catégories pour les lier à mon produit
            // via ma table de jointure ProductsCategorie grace a mon model ProductsCategories
            foreach ($categories as $cat_id) {
                ProductsCategories::create([
                    "product_id" => $produit->id,
                    "category_id" => $cat_id
                ]);
            }


            // Upload des images et liens 
            if ($request->hasFile("images")) {
                // si dan sla requete que je recois il y a un ou des champs nommés image
                // je fais un foreach sur le tableau créé par la fonction file()
                // qui contient tous les files de ma requete
                foreach ($request->file("images") as $image) {
                    $img = ImagesController::uploadSingleImageInPublicStorageAndBDD($image);
                    if ($img == null) {
                        return response()->json([
                            "message" => "Erreur dans la création des images",
                            "produit" => "error"
                        ],400);
                    }
                    // je crée la relation
                    ProductsImages::create([
                        "product_id" => $produit->id,
                        "image_id" => $img->id
                    ]);
                }
            }

            return response()->json([
                "message" => "Produit ajouté avec succès",
                "produit" => $produit
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                "message"=> "Erreur durant l'ajout du produit :".$th,
                "produit" => "error"
            ]);
        }
    }

    /**
     * Supprime un produit de la base de données via son ID dans l'url
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function deleteProduct(string $id){
        try {
            //Je vérifie que l'id est un nombre
            if (!is_numeric($id)) {
                return response()->json([
                    "message" => "L'id du produit ne correspond à aucun produit",
                    "product" => "error"
                ], 400);
            }
            $product = Products::find($id);
            //si le produit existe je le delete
            if ($product) {
                // avant de delete le produit je supprime les image qui lui sont associées dans la bdd et dans le storage
                $images = ProductsImages::where("product_id", $product->id)->pluck("image_id");
                foreach ($images as $imageId) {
                    ImagesController::deleteImageInPublicStorageAndBDD($imageId);
                }
                // une fois le simage supprimé dans le storage et la table images je supprime le produit
                // qui supprimera en cascade les relations dans la table de jointure des catégories et des images
                $product->delete();
                return response()->json([
                    "message"=> "Le produit a bien été supprimé",
                    "product"=> $product
                ]);
            }
            //si le produit existe pas je renvoi un message d'erreur
            else{
                return response()->json([
                    "message"=> "Le produit n'existe pas",
                    "product" => "error"
                ]);
        }
        } catch (\Throwable $th) {
            return response()->json([
                "message"=> "Erreur durant la suppression du produit :".$th,
                "product"=> "error"
            ]);
        }
    }


    public function getAllProducts(){
        try {
            $products = Products::all();
            return response()->json([
                "message" => "Produits trouvés",
                "products" => $products
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la récupération des produits",
                "products" => ""
            ]);
        }
    }

    /**
     * Récupère un produit à partir de son ID dans l'url
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getProductById(string $id){
        try {
            //Je vérifie que l'id est un nombre
            if (!is_numeric($id)) {
                return response()->json([
                    "message" => "L'id du produit ne correspond à aucun produit",
                    "product" => "error"
                ], 400);
            }
            $product = Products::find($id);
            //si le produit existe je le le return
            if ($product) {
                // je récupère les catégories associées via la table de jointure
                // SELECT images.id, images.url
                // FROM _products_images
                // JOIN images ON _products_images.image_id = images.id
                // WHERE _products_images.product_id = $id;
                $categories = ProductsCategories::where("product_id", $id)
                ->join("categories", "categories.id", "=", "_products_categories.category_id")
                ->select("categories.id", "categories.name")
                ->get();
                // je recupere les images liées via la table de jointure
                // SELECT images.id, images.url
                //FROM _products_images
                // JOIN images ON _products_images.image_id = images.id
                // WHERE _products_images.product_id = $id;
                $images = ProductsImages::where('product_id', $id)
                ->join('images', 'images.id', '=', '_products_images.image_id')
                ->select('images.id', 'images.url')
                ->get();
                // on ajoute les catégories au produit
                $product->categories = $categories;
                $product->images = $images;
                return response()->json([
                    "message"=> "Produit trouvé",
                    "product"=> $product
                ],200);
            }
            //si le produit existe pas je renvoi un message d'erreur
            else{
                return response()->json([
                    "message"=> "Le produit n'existe pas",
                    "product" => "error"
                ],400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message"=> "Erreur durant la récupération du produit :".$th,
                "product"=> "error"
            ],500);
        }
    }

    /**
     * Met à jour un produit dans la base de données via son ID dans l'url
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateProduct(Request $request, string $id)
    {
        // Log::info("body de la requête : " . json_encode($request->all()));
        try {
            if (!is_numeric($id)) {
                return response()->json([
                    "message" => "ID invalide.",
                    "product" => "error"
                ], 400);
            }

            $product = Products::find($id);
            if (!$product) {
                return response()->json([
                    "message" => "Produit introuvable.",
                    "product" => "error"
                ], 404);
            }

            // Validation
            $validatedRequest = $request->validate([
                "name" => "nullable|string|max:255",
                "description" => "nullable|string|max:1000",
                "prix" => "|numeric|min:0"
            ]);
            
            if ($validatedRequest["name"] != null) {
                if (preg_match('/(<\?php|<script|<\/script>|onerror=|onload=|javascript:|[\'^£$%&*()}{@#~?><>,|=_+¬-])/i', $validatedRequest["name"])) {
                    return response()->json([
                        "message" => "Erreur dans la modification du nom du produit",
                        "product" => "error"
                    ], 400);
                }
                $product->name = $validatedRequest["name"];
            }

            if ($validatedRequest["description"] != null) {
                if (preg_match('/(<\?php|<script|<\/script>|onerror=|onload=|javascript:|[\'^£$%&*()}{@#~?><>,|=_+¬-])/i', $validatedRequest["description"])) {
                    return response()->json([
                        "message" => "Erreur dans la modification de la description",
                        "product" => "error"
                    ], 400);
                }
                $product->description = $validatedRequest["description"];
            }

            if ($validatedRequest["prix"] > 0) {
                $product->price = $validatedRequest["prix"];
            }
            $product->save();

            // Traitement des catégories
            $categoryIds = json_decode($request->input("categories"), true); // tableau d'ID finaux

            // Récupérer les anciennes catégories
            $currentCategories = ProductsCategories::where("product_id", $product->id)->pluck("category_id")->toArray();

            $toAdd = array_diff($categoryIds, $currentCategories);
            $toDelete = array_diff($currentCategories, $categoryIds);

            foreach ($toDelete as $cat_id) {
                ProductsCategories::where("product_id", $product->id)
                    ->where("category_id", $cat_id)
                    ->delete();
            }

            foreach ($toAdd as $cat_id) {
                ProductsCategories::create([
                    "product_id" => $product->id,
                    "category_id" => $cat_id
                ]);
            }

            // Suppression des images
            $toDeleteImages = $request->input("imageToDelete");
            if ($toDeleteImages && is_array($toDeleteImages)) {
                foreach ($toDeleteImages as $image_id) {
                    $existingRelation = ProductsImages::where("product_id", $product->id)
                        ->where("image_id", $image_id)
                        ->first();
                    if ($existingRelation) {
                        $existingRelation->delete();
                        if (ImagesController::deleteImageInPublicStorageAndBDD($image_id)==false) {
                            return response()->json([
                                "message" => "Erreur dans la suppression de l'image",
                                "product" => "error"
                            ], 400);   
                        }
                    }
                }
            }

            // Ajout des images
            if ($request->hasFile("imageToAdd")) {
                $toAddImages = $request->file("imageToAdd");
                foreach ($toAddImages as $image) {
                    $img = ImagesController::uploadSingleImageInPublicStorageAndBDD($image);
                    if ($img == null) {
                        return response()->json([
                            "message" => "Erreur dans la création des images",
                            "product" => "error"
                        ], 400);
                    }

                    ProductsImages::create([
                        "product_id" => $product->id,
                        "image_id" => $img->id
                    ]);
                }
            }

            return response()->json([
                "message" => "Produit mis à jour avec succès.",
                "product" => $toDeleteImages
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur lors de la mise à jour du produit : " . $th->getMessage(),
                "product" => "error"
            ], 500);
        }
    }

    

    /**
     * Récupère le nombre total de produits dans la base de données
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function count (Request $request) {
        try {
            $count = Products::count();
            return response()->json([
                "message" => "Le nombre de produits a été récupéré avec succès",
                "count" => $count
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la récupération du nombre de catégories : " . $th->getMessage(),
                "count" => 0
            ], 500);
        }
    }

    /**
     * Vérifie si le nom du produit existe déjà dans la base de données
     * @param string $name
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function checkIfProductNameExists(string $name){
        try {
            // Je vérifie que le nom du produit n'est pas vide
            if (empty($name)) {
                return response()->json([
                    "message" => "Le nom du produit ne peut pas être vide"
                ], 400);
            }
            // Je vérifie que le nom ne contient pas de caractères interdits
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $name)) {
                return response()->json([
                    "message" => "Le nom du produit contient des caractères interdits"
                ], 400);
            }
            // Je cherche le produit dans la base de données
            $product = Products::where("name", $name)->first();
            if ($product) {
                return response()->json([
                    "message" => "Le produit existe déjà"
                ], 404);
            } else {
                return response()->json([
                    "message" => "Le produit n'existe pas"
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Erreur durant la vérification de l'existence du nom du produit : " . $th->getMessage(),
            ], 500);
        }
    }
}
