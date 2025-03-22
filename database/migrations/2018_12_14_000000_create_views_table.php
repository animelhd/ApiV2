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
        Schema::create(config('animesfavorite.favorites_table'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger(config('animesfavorite.user_foreign_key'))->index()->comment('user_id');
            $table->morphs('favoriteable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('animesfavorite.favorites_table'));
    }
};
