<?php

namespace Retamayo\Htmplate\Classes;

use Retamayo\Htmplate\Exceptions\HtmplateException;
use Retamayo\Htmplate\Classes\PathResolver;
use Retamayo\Htmplate\Classes\File;

class Cache
{

    public function getCache(string $view): string
    {
        try {
            if (!is_dir(PathResolver::getCachePath())) {
                throw new HtmplateException('File Not Found', 'The directory you are trying to access does not exist', 1001);
            }

            $latestModificationTime = 0;
            $latestCachePath = '';

            foreach (new \DirectoryIterator(PathResolver::getCachePath()) as $file) {
                if (!$file->isDot() && $file->isFile()) {
                    $modificationTime = $file->getMTime();
                    $cacheName = $file->getFilename();
                    $cacheNameParts = explode('_', $cacheName);

                    // the view name of cache file must be always in the third position
                    $cacheViewName = $cacheNameParts[2];
                    $cacheViewName = pathinfo($cacheViewName, PATHINFO_FILENAME); // remove the file extension if any

                    if ($cacheViewName == $view && $modificationTime > $latestModificationTime) {
                        $latestModificationTime = $modificationTime;
                        $latestCachePath = $file->getPathname();
                    }
                }
            }

            return $latestCachePath; // returns an empty string if the cache is not found.
        } catch (HtmplateException $e) {
            $e->render();
            return ''; // returns empty in case of an exception.
        }
    }


    public function setCache(string $viewName, string $content): void
    {
        try {
            $cachePath = $this->getCache($viewName);
    
            if ($cachePath !== '') {
                // if a cache file already exists for the view, update it
                File::writeFile($cachePath, $content);
            } else {
                // if no cache file exists, create a new one
                $cacheFullPath = PathResolver::getCachePath() . '/' . uniqid('cache_') . '_' . $viewName . '.php';
                File::createFile($cacheFullPath);
                File::writeFile($cacheFullPath, $content);
            }
        } catch (HtmplateException $e) {
            $e->render();
        }
    }

    public function purgeCache(): void
    {
        foreach (new \DirectoryIterator(PathResolver::getCachePath()) as $file) {
            if (!$file->isDot()) {
                if (filectime($file->getPathname()) + 100 < time()) {
                    File::deleteFile($file->getPathname());
                }
            }
        }
    }
}
