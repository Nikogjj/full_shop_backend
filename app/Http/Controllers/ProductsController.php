<?php

namespace App\Http\Controllers;

use App\Models\Images;
use App\Models\Products;
use App\Models\ProductsCategories;
use App\Models\ProductsImages;
use File;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
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
     * Ajoute un ou plusieurs produits dans la base de données a partir d'un tableau JSON de produits dans le body de la requête
     * @param \Illuminate\Http\Request $request
     * @return array|mixed|\Illuminate\Http\JsonResponse
     */
    public function addProducts(Request $request){
        // J'attend un tab JSON avec "name", "description", "price", "category_id" et "image" (optionnel)
        try {
        $validatedRequest = $request->validate([
            '*.name' => 'required|string|max:255',
            '*.description' => 'required|string|max:500',
            '*.price' => 'required|numeric|min:0',
            '*.tab_categories' => 'required|array',
            '*.tab_categories.*.category_id' => 'required|integer|exists:categories,id',
            '*.tab_images_url' => 'nullable|array',
            '*.tab_images_url.*.image_url' => 'nullable|string|max:500',
        ]);

        $validatedRequestLenght = count($validatedRequest);
        $tabcreatedProducts = array();

        for ($i=0; $i < $validatedRequestLenght; $i++) { 
            $name = $validatedRequest[$i]["name"];
            $description = $validatedRequest[$i]["description"];
            $price = $validatedRequest[$i]["price"];
            $tab_categories = $validatedRequest[$i]["tab_categories"];
            $tab_categoriesLenght = count($tab_categories);
            $tab_images_url = $validatedRequest[$i]["tab_images_url"] ?? null;
            $tab_images_urlLenght = $tab_images_url ? count($tab_images_url) : 0;
            //Je crée mon produit dans la base de données
            $product = Products::create([
                "name" => $name,
                "description" => $description,
                "price" => $price,
            ]);
            $product->save();
            array_push($tabcreatedProducts, $product);

            //Je lie les ou la catégorie(s) au produit
            for ($k=0; $k < $tab_categoriesLenght; $k++) { 
                $rowCreated = ProductsCategories::create([
                    "product_id" => $product->id,
                    "category_id" => $tab_categories[$k]["category_id"]
                ]);
                $rowCreated->save();
                array_push($tabcreatedProducts, $rowCreated);
            }
            array_push($tabcreatedProducts, $validatedRequest[$i]["name"]);

            //Je lie les images au produit
            //DEMANDER A MASSI SI CEST BIEN DE CHERCHER LES IMAGES COMME CA
            for ($l=0; $l < $tab_images_urlLenght; $l++) { 
                $image = Images::select("id")->where("url", $tab_images_url[$l]["image_url"])->first();
                $rowCreated = ProductsImages::create([
                    "product_id"=> $product->id,
                    "image_id" => $image->id
                ]);
                $rowCreated->save();
                array_push($tabcreatedProducts, $rowCreated);
            }
        }
            return $tabcreatedProducts;
            
        } catch (\Throwable $th) {
            return response()->json([
                "message"=> "Erreur durant l'ajout du produit :".$th,
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
}
