<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('local_uuid')->unique();
            $table->foreignId('salesperson_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->string('shop_name_snapshot')->nullable();
            $table->date('order_date');
            $table->text('notes')->nullable();
            $table->string('discount_type')->default('none'); // none | percent | amount
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('sync_status')->default('synced'); // synced | edited
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
