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
        Schema::create('wrapped_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wrapped_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('stat'); // music | place | milestone | stat | message
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('meta')->nullable(); // campos flexíveis por tipo (ex.: artista, cidade, número)
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrapped_slides');
    }
};
