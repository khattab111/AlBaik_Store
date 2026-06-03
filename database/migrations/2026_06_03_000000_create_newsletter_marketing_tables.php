<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            if (! Schema::hasColumn('newsletter_subscribers', 'name')) {
                $table->string('name')->nullable()->after('email');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'phone')) {
                $table->string('phone')->nullable()->after('name');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'status')) {
                $table->enum('status', ['active', 'unsubscribed', 'bounced'])->default('active')->index()->after('locale');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'source')) {
                $table->string('source')->nullable()->index()->after('status');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'verification_token')) {
                $table->string('verification_token')->nullable()->after('source');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verification_token');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'unsubscribe_token')) {
                $table->string('unsubscribe_token')->nullable()->unique()->after('verified_at');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('unsubscribe_token');
            }

            if (! Schema::hasColumn('newsletter_subscribers', 'metadata')) {
                $table->json('metadata')->nullable()->after('unsubscribed_at');
            }
        });

        Schema::create('newsletter_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject_ar')->nullable();
            $table->string('subject_en')->nullable();
            $table->string('preheader_ar')->nullable();
            $table->string('preheader_en')->nullable();
            $table->longText('content_ar');
            $table->longText('content_en')->nullable();
            $table->json('design')->nullable();
            $table->enum('category', ['offers', 'new_products', 'announcement', 'welcome', 'abandoned_cart', 'custom'])->default('custom')->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->boolean('is_default')->default(false)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('template_id')->nullable()->constrained('newsletter_templates')->nullOnDelete();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('content');
            $table->string('locale', 12)->default('ar')->index();
            $table->json('audience')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'queued', 'sending', 'sent', 'cancelled', 'failed'])->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('stats')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->nullable()->constrained('newsletter_subscribers')->nullOnDelete();
            $table->string('email')->index();
            $table->string('subject');
            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['subscriber_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_templates');

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $columns = [
                'metadata',
                'unsubscribed_at',
                'unsubscribe_token',
                'verified_at',
                'verification_token',
                'source',
                'status',
                'phone',
                'name',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('newsletter_subscribers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
