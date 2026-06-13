<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->decimal('balance', 15, 2)->default(0);
                $table->string('currency_code', 3)->nullable()->index();
                $table->enum('status', ['active', 'frozen', 'disabled'])->default('active')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table): void {
                $table->id();
                $table->uuid('transaction_number')->unique();
                $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('type', ['deposit', 'purchase', 'refund', 'adjustment', 'bonus', 'hold', 'release', 'withdraw'])->index();
                $table->enum('direction', ['credit', 'debit'])->index();
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_before', 15, 2);
                $table->decimal('balance_after', 15, 2);
                $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed')->index();
                $table->nullableMorphs('reference');
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['wallet_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
