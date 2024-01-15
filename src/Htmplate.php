<?php

namespace Retamayo\Htmplate;

use Retamayo\Htmplate\Classes\PathResolver;
use Retamayo\Htmplate\Classes\File;
use Retamayo\Htmplate\Classes\Renderer;
use Retamayo\Htmplate\Classes\Transpiler;
use Retamayo\Htmplate\Classes\Cache;
use DOMDocument;

class Htmplate
{
    private Renderer $renderer;

    public function __construct()
    {
        File::createPath(PathResolver::getCachePath());
        File::createPath(PathResolver::getViewPath());
        $this->renderer = new Renderer(new Transpiler(new DOMDocument()), new Cache());
    }

    public function render($view, $data = [])
    {
        $this->renderer->renderView($view, $data);
    }
}