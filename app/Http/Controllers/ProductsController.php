<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Storage;

class ProductsController extends Controller
{
    /**
     * Récupère un produit à partir de son ID dans le query param de l'URL.
     * @param string $id
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getProduct(string $id,Request $request){
        //Je vérifie que l'id est un nombre
        if (!is_numeric($id)) {
            return response()->json([
                "message" => "L'id du produit n'existe pas",
                "products"  => "error"
            ], 400);
        }
        try {
            $product = Products::find($id);
            return response()->json([
                "message"=> "Produit trouvé",
                "products" => $product
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=> "Erreur durant la recherche du produit",
                "products" => "error"
            ]);
        }
    }

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

    public function addProducts(Request $request){
        $validatedRequest = $request -> validate([
            "*name" => "required|string|max:255",
            "*description" => "required|string|max:500",
            "*price" => "required|float|min:0",
            "*category_id" => "required|exists:categories,id",
            "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5000",
        ]);
    }

    public function test(Request $request){
        $test = $request->file("image")->store("/storage/app/public/images");

        return $test;
    }
}
