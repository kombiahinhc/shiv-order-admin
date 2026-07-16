<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('gst_number')->nullable()->after('address');
            $table->string('image_path')->nullable()->after('gst_number');
            $table->decimal('latitude', 10, 7)->nullable()->after('image_path');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['gst_number', 'image_path', 'latitude', 'longitude']);
        });
    }
};
