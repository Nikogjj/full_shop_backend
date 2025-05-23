<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table("_reviews_users_products", function (Blueprint $table) {
            DB::statement('ALTER TABLE _reviews_users_products ADD CONSTRAINT chk_rating CHECK (rating BETWEEN 0 AND 5)');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
