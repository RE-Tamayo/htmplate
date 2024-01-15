<?php

namespace Retamayo\Htmplate\Classes;

use Retamayo\Htmplate\Classes\PathResolver;
use Retamayo\Htmplate\Classes\Transpiler;
use Retamayo\Htmplate\Exceptions\HtmplateException;
use Retamayo\Htmplate\Classes\Cache;
use Retamayo\Htmplate\Classes\Observer;

class Renderer
{
    private Transpiler $transpiler;
    private Cache $cache;

    public function __construct(Transpiler $transpiler, Cache $cache)
    {
        $this->transpiler = $transpiler;
        $this->cache = $cache;
    }

    public function getView(string $viewName): string
    {
        try {
            $view = PathResolver::getViewPath() . '/' . $viewName . '.html';
            if (!file_exists($view)) {
                throw new HtmplateException('File Not Found', 'The file you are trying to read does not exist', 1001);
            }
            return $view;
        } catch (HtmplateException $e) {
            $e->render();
        }
    }

    public function renderView(string $viewName, array $data = []): void
    {
        $this->cache->purgeCache();
        if (Observer::hasChanged($this->getView($viewName), $this->cache->getCache($viewName))) {
            $content = $this->transpiler->transpile($this->getView($viewName), $data);
            // File::writeFile($this->cache->getCache($viewName), $content);
            $this->cache->setCache($viewName, $content);
            ob_start();
            include $this->cache->getCache($viewName);
            echo ob_get_clean();
        } else {
            ob_start();
            include $this->cache->getCache($viewName);
            echo ob_get_clean();
        }
        
    }
}