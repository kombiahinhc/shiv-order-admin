<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('mrp', 12, 2)->nullable()->after('list_price');
            $table->string('image_path')->nullable()->after('active');
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->boolean('is_tax_inclusive')->default(false)->after('tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['mrp', 'image_path']);
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('is_tax_inclusive');
        });
    }
};
