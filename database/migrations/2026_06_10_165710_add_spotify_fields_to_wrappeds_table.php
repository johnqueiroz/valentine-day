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
            $table->string('gifter_name')->nullable()->after('couple_name_2');
            $table->string('song_title')->nullable()->after('gifter_name');
            $table->string('song_artist')->nullable()->after('song_title');
            $table->string('youtube_url')->nullable()->after('song_artist');
            $table->text('love_letter')->nullable()->after('youtube_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wrappeds', function (Blueprint $table) {
            $table->dropColumn([
                'gifter_name',
                'song_title',
                'song_artist',
                'youtube_url',
                'love_letter',
            ]);
        });
    }
};
