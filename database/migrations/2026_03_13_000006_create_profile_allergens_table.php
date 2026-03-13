<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_allergens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['profile_id', 'allergen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_allergens');
    }
};