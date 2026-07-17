<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $table->string('new_api_url')->nullable()->after('is_force_update');
        });
    }

    public function down(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $table->dropColumn('new_api_url');
        });
    }
};
