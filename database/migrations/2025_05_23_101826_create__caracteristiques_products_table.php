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
        Schema::create('_caracteristiques_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId("product_id")->constrained("products","id");
            $table->foreignId("caracteristique_id")->constrained("caracteristiques","id");
            $table->string("value");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_caracteristiques_products');
    }
};
