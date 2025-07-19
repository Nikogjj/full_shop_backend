<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Images;
use Log;

class ImagesController extends Controller
{
        /**
     * Upload une image dans le storage public et l'enregistre dans la base de données
     * @param \Illuminate\Http\Request $request
     * @return Images|null
     */
    public static function uploadSingleImageInPublicStorageAndBDD(UploadedFile $image)
    {
        try {
            // on vérifie que c'est bien une image valide (mimes + taille)
            $extension = $image->getClientOriginalExtension();
            $allowedExtensions = ["jpeg", "jpg", "png", "gif", "svg", "webp"];
    
            // Vérifie si le type mime de l'image est le bon en le mettant en minuscule et en le comparant 
            // à mon tab alloowedExtensions pour voir si il est dedans
            // et vérifie que l'image ne dépasse pas 5mo
            if (!in_array(strtolower($extension), $allowedExtensions) || $image->getSize() > 5 * 1024 * 1024) {
                return null;
            }
    
            // On génère un nom de fichier unique et on le stocke dans le disque public (dossier images)
            $fileName = $image->hashName();
            $storagePath = $image->storeAs("images", $fileName, "public");
            $publicUrl = "storage/" . $storagePath;
    
            // On crée l'enregistrement dans la BDD
            $imageModel = Images::create([
                "url" => $publicUrl,
            ]);

            return $imageModel;
    
        } catch (\Throwable $th) {
            return null;
        }
    }
    

    /**
     * Supprime une image du storage public et de la base de données via son ID
     * @param \Illuminate\Http\Request $request
     * @return boolean
     */
    public static function deleteImageInPublicStorageAndBDD(string $id){
        try {
            if (!is_numeric($id)) {
                return false;
            }
    
            $image = Images::find($id);
    
            if ($image) {
                Log::info("url de l'image à supprimer : " . $image->url);
                // Je récupére seulement le nom du fichier à partir de l'url
                $imageName = basename($image->url);
                // suppression du fichier physique
                if ($imageName && Storage::disk('public')->exists("images/".$imageName)) {
                    Storage::disk('public')->delete("images/".$imageName);
                }
                else{
                    return false;
                }
                // suppression de l'entrée en BDD
                $image->delete();
    
                return true;
            } else {
                return false;
            }
        } 
        catch (\Throwable $th) {
            return false;
        }
    }

    public function testDeleteImageInPublicStorageAndBDD(Request $request)
    {
        $url = $request->input('url');
        $imageName = basename($url);
        return $imageName;
    }
}
