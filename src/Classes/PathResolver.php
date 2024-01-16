<?php

namespace Retamayo\Htmplate\Classes;

class PathResolver
{
    public static function getBasePath()
    {
        return dirname(dirname(dirname(dirname(dirname(__DIR__)))));
    }

    public static function getLibPath()
    {
        return dirname(__DIR__);
    }

    public static function getViewPath()
    {
        return self::getBasePath() . '/views';
    }

    public static function getCachePath()
    {
        return self::getBasePath() . '/cache';
    }

    public static function resolvePath(string $path): string
    {
        return __DIR__ . '/' . $path;
    }
}