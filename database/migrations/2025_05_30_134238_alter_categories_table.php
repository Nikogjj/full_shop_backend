<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Ajout de la colonne parent_id qui pointe vers l'id d'une categorie (dans la même table)
            // Quand je suppime une catégorie, je mets à null la colonne parent_id des catégories qui étaient ses enfants
            $table->foreignId("parent_id")->nullable()->constrained("categories","id")->nullOnDelete();
            // Ajout de la colonne nombre_de_parents qui permet de savoir combien de parents a une catégorie
            // et du coup savoir à quel niveau elle se trouve dans la hiérarchie
            // Par défaut elle est initialisé à 0
            $table->integer("nombre_de_parents")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
