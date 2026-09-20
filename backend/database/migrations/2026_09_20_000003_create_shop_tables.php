<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('customer')->index()->after('phone');
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->string('image')->nullable(); $table->boolean('status')->default(true)->index(); $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->foreignId('category_id')->constrained()->restrictOnDelete(); $table->string('name'); $table->string('slug')->unique(); $table->string('sku')->unique(); $table->text('description')->nullable(); $table->decimal('price', 12, 2); $table->decimal('sale_price', 12, 2)->nullable(); $table->unsignedInteger('stock')->default(0); $table->string('image')->nullable(); $table->boolean('status')->default(true)->index(); $table->timestamps(); $table->index(['status', 'category_id']);
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); $table->string('order_number')->unique(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('customer_name'); $table->string('phone'); $table->string('email')->nullable(); $table->string('district'); $table->string('khoroo'); $table->text('address'); $table->text('note')->nullable(); $table->decimal('subtotal', 12, 2); $table->decimal('delivery_fee', 12, 2); $table->decimal('total', 12, 2); $table->string('status')->default('NEW')->index(); $table->string('payment_status')->default('pending')->index(); $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete(); $table->string('product_name'); $table->unsignedInteger('quantity'); $table->decimal('unit_price', 12, 2); $table->decimal('subtotal', 12, 2); $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->string('method'); $table->decimal('amount', 12, 2); $table->string('transaction_id')->nullable(); $table->string('status')->default('pending')->index(); $table->string('proof_image')->nullable(); $table->timestamp('paid_at')->nullable(); $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); $table->string('key')->unique(); $table->text('value')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings'); Schema::dropIfExists('payments'); Schema::dropIfExists('order_items'); Schema::dropIfExists('orders'); Schema::dropIfExists('products'); Schema::dropIfExists('categories');
        Schema::table('users', function (Blueprint $table) { $table->dropColumn(['phone', 'role']); });
    }
};