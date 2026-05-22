<?php

namespace App\Traits;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait TranslationTrait
{
    protected static function getClassNameWithCapability(string $capability): string
    {
        return class_basename(static::class).$capability;
    }

    protected static function translateLabel(string $prefix, string $capability): string
    {
        $coreKey = 'core.'.$prefix.static::getClassNameWithCapability($capability);

        if (Lang::has($coreKey)) {
            return __($coreKey);
        }

        $resource = Str::of(class_basename(static::class))
            ->beforeLast('Resource')
            ->kebab()
            ->plural()
            ->toString();

        $adminKey = $capability === 's'
            ? "admin.resources.{$resource}.plural"
            : "admin.resources.{$resource}.singular";

        if (Lang::has($adminKey)) {
            return __($adminKey);
        }

        return Str::of(class_basename(static::class))
            ->beforeLast('Resource')
            ->headline()
            ->toString();
    }

    public static function getLabel(): ?string
    {
        return static::translateLabel('', '');
    }

    public static function getModelLabel(): string
    {
        return static::translateLabel('', '');
    }

    public static function getNavigationLabel(): string
    {
        return static::translateLabel('', 's');
    }

    public static function getPluralModelLabel(): string
    {
        return static::translateLabel('', 's');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return static::translateLabel('', '');
    }

    public static function getNewLabel(string $prefix): string
    {
        return static::translateLabel($prefix, '');
    }

    public static function getNavigationGroup(): ?string
    {
        $group = static::$navigationGroup ?? null;

        if (! $group) {
            return null;
        }

        $coreKey = 'core.'.$group;

        if (Lang::has($coreKey)) {
            return __($coreKey);
        }

        $adminKey = "admin.groups.{$group}";

        return Lang::has($adminKey) ? __($adminKey) : $group;
    }
}
