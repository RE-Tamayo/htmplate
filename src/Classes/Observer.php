<?php

namespace Retamayo\Htmplate\Classes;

use Retamayo\Htmplate\Exceptions\HtmplateException;

class Observer
{
    public static function hasChanged(string $view, string $cache): bool
    {
        try {
            if (!file_exists($view)) {
                throw new HtmplateException('File Not Found', 'The file you are trying to watch does not exist', 1001);
            }
            if (!file_exists($cache)) {
                return true; // return true if there is no cache for the view
            }
            $viewModificationTime = filemtime($view);
            $cacheModificationTime = filemtime($cache);
            if ($viewModificationTime > $cacheModificationTime) {
                return true; // return true if view needs to be updated
            }
            // Check modification time for included files recursively
            $content = File::readFile($view);

            $pattern = '/<include\s+path\s*=\s*["\']([^""]*)["\']\s*>/';
            preg_match_all($pattern, $content, $matches);
            foreach ($matches[1] as $includePath) {
                $includeFile = PathResolver::getViewPath() . '/' . $includePath . '.html'; // Adjust this according to your file structure
    
                if (file_exists($includeFile) && filemtime($includeFile) > $cacheModificationTime) {
                    return true; // return true if an included file needs to be updated
                }
            }
            return false; // return false if view does not need to be updated
        } catch (HtmplateException $e) {
            $e->render();
        }
    }

    
}