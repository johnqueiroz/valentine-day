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
        Schema::create('wrappeds', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('couple_name_1');
            $table->string('couple_name_2');
            $table->date('relationship_started_on')->nullable();
            $table->string('theme')->default('green'); // cor de acento (skin Spotify)
            $table->string('cover_photo_path')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrappeds');
    }
};
