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
        Schema::create(config('animeswatchlater.watchlaters_table'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger(config('animeswatchlater.user_foreign_key'))->index()->comment('user_id');
            $table->morphs('watchlaterable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('animeswatchlater.watchlaters_table'));
    }
};
