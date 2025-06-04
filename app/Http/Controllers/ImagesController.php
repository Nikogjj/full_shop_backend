<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Images;

class ImagesController extends Controller
{
        /**
     * Upload une image dans le storage public et l'enregistre dans la base de données
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function uploadImageInPublicStorageAndBDD(Request $request){
        //j'attend un formdata avec le file "image" avec max 5Mo
        try {
            $validatedRequest = $request->validate([
                "image" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:5000",
            ]);
            $file = $validatedRequest["image"];
            $name = $file->hashName();
            $publicPath ="storage/".$file->storeAs("images", $name ,"public");
            if ($publicPath != null) {
                $image = Images::create([
                    "url" => $publicPath,
                ]);
                $image->save();
                return response()->json([
                    "message"=> "Image ajoutée avec succès",
                    "image" => $image,
                ], 201);
            }
            else {
                return response()->json([
                    "message"=> "Erreur durant l'ajout de l'image",
                ],500);   
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message"=> "Erreur durant l'ajout de l'image :".$th,
            ]);
        }
    }

    /**
     * Supprime une image du storage public et de la base de données via son ID dans l'url
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function deleteImageInPublicStorageAndBDD(string $id){
        try {
            //Je vérifie que l'id est un nombre
            if (!is_numeric($id)) {
                return response()->json([
                    "message" => "L'id de l'image ne correspond à aucune image",
                    "image" => "error"
                ], 400);
            }
            $image = Images::find($id);
            //si le produit existe je le delete
            if ($image) {
                $image->delete();
                return response()->json([
                    "message"=> "L'image a bien été supprimée",
                    "image"=> $image
                ]);
            }
            //si le produit existe pas je renvoi un message d'erreur
            else{
                return response()->json([
                    "message"=> "L'image n'existe pas",
                    "image" => "error"
                ]);
            }
        } 
        catch (\Throwable $th) {
            //throw $th;
        }
    }
}
