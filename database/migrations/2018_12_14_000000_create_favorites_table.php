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
        Schema::create(config('animesview.views_table'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger(config('animesview.user_foreign_key'))->index()->comment('user_id');
            $table->morphs('vieweable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('animesview.views_table'));
    }
};
