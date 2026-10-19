<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('role', 30)->default('banner');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['category_id', 'media_asset_id']);
            $table->index(['category_id', 'role', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_media');
    }
};
