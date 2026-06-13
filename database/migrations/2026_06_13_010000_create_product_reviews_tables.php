<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(0)->after('is_wholesale_available')->index();
            }

            if (! Schema::hasColumn('products', 'reviews_count')) {
                $table->unsignedInteger('reviews_count')->default(0)->after('average_rating')->index();
            }
        });

        Schema::create('product_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'hidden'])->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
            $table->index(['product_id', 'status']);
            $table->index(['rating', 'status']);
        });

        Schema::create('product_review_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_review_id')->constrained('product_reviews')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_review_images');
        Schema::dropIfExists('product_reviews');

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'reviews_count')) {
                $table->dropColumn('reviews_count');
            }

            if (Schema::hasColumn('products', 'average_rating')) {
                $table->dropColumn('average_rating');
            }
        });
    }
};
