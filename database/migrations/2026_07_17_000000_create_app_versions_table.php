<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version'); // e.g., 1.0.0
            $table->integer('build_number'); // e.g., 1, 2, 3
            $table->string('apk_path'); // path to APK file
            $table->text('release_notes')->nullable();
            $table->boolean('is_force_update')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
