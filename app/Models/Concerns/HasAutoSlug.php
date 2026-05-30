<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait HasAutoSlug
{
    public static function bootHasAutoSlug(): void
    {
        static::creating(function (Model $model): void {
            $model->generateSlugIfMissing();
        });
    }

    public function generateSlugIfMissing(): void
    {
        if (! $this->hasSlugColumn() || filled($this->getAttribute('slug'))) {
            return;
        }

        $sourceField = property_exists($this, 'slugSourceField')
            ? $this->slugSourceField
            : 'name';

        $base = $this->slugSourceValue($sourceField);
        $slug = Str::slug($base) ?: 'item';

        $this->setAttribute('slug', $this->uniqueSlug($slug));
    }

    private function slugSourceValue(string $field): string
    {
        $raw = $this->getAttributes()[$field] ?? $this->getAttribute($field);
        $translations = $this->normalizeSlugSource($raw);

        return (string) (
            $translations['en']
            ?? $translations['ar']
            ?? collect($translations)->first(fn ($value) => filled($value))
            ?? 'item'
        );
    }

    private function normalizeSlugSource(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return ['en' => $value];
        }

        return [];
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while ($this->newQueryWithoutScopes()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function hasSlugColumn(): bool
    {
        return Schema::hasColumn($this->getTable(), 'slug');
    }
}
