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
        Schema::create("_products_images", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId("product_id")->constrained("products", "id")->onDelete("cascade");

            $table->foreignId("image_id")->constrained("images","id")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_products_images');
    }
};
