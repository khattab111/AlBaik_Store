<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 12)->unique();
            $table->enum('direction', ['rtl', 'ltr'])->default('ltr');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('flag', 16)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('languages')->insert([
            ['name' => 'العربية', 'code' => 'ar', 'direction' => 'rtl', 'is_default' => true, 'is_active' => true, 'flag' => '🇸🇦', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'English', 'code' => 'en', 'direction' => 'ltr', 'is_default' => false, 'is_active' => true, 'flag' => '🇺🇸', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->jsonize('categories', ['name' => 'required', 'description' => 'nullable']);
        $this->jsonize('brands', ['name' => 'required', 'description' => 'nullable']);
        $this->jsonize('tags', ['name' => 'required']);
        $this->jsonize('products', [
            'name' => 'required',
            'short_description' => 'nullable',
            'description' => 'nullable',
            'seo_title' => 'nullable',
            'seo_description' => 'nullable',
        ]);
        $this->jsonize('flash_sales', ['name' => 'required']);
        $this->jsonize('payment_methods', ['name' => 'required', 'description' => 'nullable']);
        $this->jsonize('shipping_methods', ['name' => 'required', 'description' => 'nullable']);

        $this->jsonize('banners', ['title' => 'required', 'subtitle' => 'nullable']);

        Schema::table('banners', function (Blueprint $table) {
            if (! Schema::hasColumn('banners', 'eyebrow')) {
                $table->json('eyebrow')->nullable()->after('subtitle');
            }
            if (! Schema::hasColumn('banners', 'primary_button_text')) {
                $table->json('primary_button_text')->nullable()->after('url');
            }
            if (! Schema::hasColumn('banners', 'secondary_button_text')) {
                $table->json('secondary_button_text')->nullable()->after('primary_button_text');
            }
        });

        DB::statement("UPDATE banners SET eyebrow = JSON_OBJECT('ar', COALESCE(eyebrow_ar, ''), 'en', COALESCE(eyebrow_en, '')) WHERE eyebrow IS NULL");
        DB::statement("UPDATE banners SET primary_button_text = JSON_OBJECT('ar', COALESCE(primary_button_text_ar, ''), 'en', COALESCE(primary_button_text_en, '')) WHERE primary_button_text IS NULL");
        DB::statement("UPDATE banners SET secondary_button_text = JSON_OBJECT('ar', COALESCE(secondary_button_text_ar, ''), 'en', COALESCE(secondary_button_text_en, '')) WHERE secondary_button_text IS NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }

    private function jsonize(string $table, array $columns): void
    {
        foreach ($columns as $column => $mode) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("UPDATE {$table} SET {$column} = CASE WHEN {$column} IS NULL THEN NULL ELSE JSON_OBJECT('ar', {$column}, 'en', {$column}) END WHERE JSON_VALID({$column}) = 0 OR {$column} IS NULL");
            $nullable = $mode === 'nullable' ? 'NULL' : 'NOT NULL';
            DB::statement("ALTER TABLE {$table} MODIFY {$column} JSON {$nullable}");
        }
    }
};
