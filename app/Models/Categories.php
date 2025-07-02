<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class Categories extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'nombre_de_parents'
    ];
    protected $table = 'categories';

    /**
     * Vérifie si la création d'une catégorie avec un parent_Id crée une boucle infinie 
     * en cherchant recursivement si un des parents nous ramene à nous même
     * @param mixed $newParentId
     * @return bool
     */
    public function CheckBoucleInfinie($newParentId)
    {
        if ($this->id == $newParentId) {
            return true; // une catégorie ne peut pas être son propre parent
        }

        $parent = Categories::find($newParentId);
        while ($parent) {
            if ($parent->id == $this->id) {
                return true; // on remonte et on retombe sur soi-même = boucle infiinie
            }
            $parent = $parent->parent; // relation parent définie ci-dessous avec la fonction parent()
            echo($parent);
        }
        return false;
    }

    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    /**
     * Met a jour le nombre de parents des enfants de la catégorie ciblée
     * @return void
     */
    public function decrementeEnfantRecursivement()
    {
        // Récupère tous les enfants de la catégorie actuelle
        // self permet d'appeler la méthode statique dans le contexte de la classe actuelle
        // this->id permet de récupérer l'id de l'instance actuelle
        $children = self::where('parent_id', $this->id)->get();

        foreach ($children as $child) {
            // Décrémente et sauvegarde
            // et on s'assure que le nombre de parents ne devient pas négatif grace à max()
            if ($child->nombre_de_parents > 0) {
                $child->nombre_de_parents--;
            }
            $child->save();
            // Appel récursif
            $child->decrementeEnfantRecursivement();
        }
    }
    /**
     * Incremente le nombre de parents des enfants de la catégorie ciblée
     * @return void
     */
    public function incrementeNbParentRecursivement()
    {
        // Récupère tous les enfants de la catégorie actuelle
        // self permet d'appeler la méthode statique dans le contexte de la classe actuelle
        // this->id permet de récupérer l'id de l'instance actuelle
        $children = self::where('parent_id', $this->id)->get();

        //Fais une boucle sur les enfants si il y en a
        foreach ($children as $child) {
            //J'incrémente le nombre de parents de l'enfant
            $child->nombre_de_parents ++;
            //je sauvegarde l'enfant
            $child->save();

            // Appel récursif pour descendre dans l'arbre
            $child->incrementeNbParentRecursivement();
        }
    }

}
