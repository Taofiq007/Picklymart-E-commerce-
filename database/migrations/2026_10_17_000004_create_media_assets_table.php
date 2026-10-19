<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('source_id', 191)->nullable()->unique();
            $table->string('filename', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('disk', 80)->default('public');
            $table->string('path', 255);
            $table->unique(['disk', 'path']);
            $table->string('mime_type', 120)->nullable()->index();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->char('checksum', 64)->nullable()->index();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->index(['disk', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
