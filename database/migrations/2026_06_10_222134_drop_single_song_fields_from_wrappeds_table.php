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
        Schema::table('wrappeds', function (Blueprint $table) {
            $table->dropColumn(['song_title', 'song_artist', 'youtube_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrappeds', function (Blueprint $table) {
            $table->string('song_title')->nullable();
            $table->string('song_artist')->nullable();
            $table->string('youtube_url')->nullable();
        });
    }
};
