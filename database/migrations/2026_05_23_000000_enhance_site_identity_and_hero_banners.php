<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (! Schema::hasColumn('banners', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title');
            }

            if (! Schema::hasColumn('banners', 'title_en')) {
                $table->string('title_en')->nullable()->after('title_ar');
            }

            if (! Schema::hasColumn('banners', 'subtitle_ar')) {
                $table->text('subtitle_ar')->nullable()->after('subtitle');
            }

            if (! Schema::hasColumn('banners', 'subtitle_en')) {
                $table->text('subtitle_en')->nullable()->after('subtitle_ar');
            }

            if (! Schema::hasColumn('banners', 'eyebrow_ar')) {
                $table->string('eyebrow_ar')->nullable()->after('subtitle_en');
            }

            if (! Schema::hasColumn('banners', 'eyebrow_en')) {
                $table->string('eyebrow_en')->nullable()->after('eyebrow_ar');
            }

            if (! Schema::hasColumn('banners', 'primary_button_text_ar')) {
                $table->string('primary_button_text_ar')->nullable()->after('url');
            }

            if (! Schema::hasColumn('banners', 'primary_button_text_en')) {
                $table->string('primary_button_text_en')->nullable()->after('primary_button_text_ar');
            }

            if (! Schema::hasColumn('banners', 'secondary_button_text_ar')) {
                $table->string('secondary_button_text_ar')->nullable()->after('primary_button_text_en');
            }

            if (! Schema::hasColumn('banners', 'secondary_button_text_en')) {
                $table->string('secondary_button_text_en')->nullable()->after('secondary_button_text_ar');
            }

            if (! Schema::hasColumn('banners', 'secondary_url')) {
                $table->string('secondary_url')->nullable()->after('secondary_button_text_en');
            }

            if (! Schema::hasColumn('banners', 'background_color')) {
                $table->string('background_color', 20)->nullable()->after('secondary_url');
            }

            if (! Schema::hasColumn('banners', 'text_color')) {
                $table->string('text_color', 20)->nullable()->after('background_color');
            }

            if (! Schema::hasColumn('banners', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('is_active');
            }

            if (! Schema::hasColumn('banners', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $columns = [
                'title_ar',
                'title_en',
                'subtitle_ar',
                'subtitle_en',
                'eyebrow_ar',
                'eyebrow_en',
                'primary_button_text_ar',
                'primary_button_text_en',
                'secondary_button_text_ar',
                'secondary_button_text_en',
                'secondary_url',
                'background_color',
                'text_color',
                'starts_at',
                'ends_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('banners', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
