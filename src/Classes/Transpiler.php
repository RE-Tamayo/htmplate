<?php

namespace Retamayo\Htmplate\Classes;

use Retamayo\Htmplate\Classes\File;
use Retamayo\Htmplate\Classes\PathResolver;
use Retamayo\Htmplate\Exceptions\HtmplateException;

class Transpiler
{
    public function transpile(string $viewPath, array $data = []): string
    {
        $viewName = explode('/', $viewPath);
        $viewName = end($viewName);
        $content = File::readFile($viewPath);
        $content = $this->transpileElementsWithVarAttr($content, $data);
        $content = $this->transpileIncludes($content, $data);
        $content = $this->transpileIf($content, $data);
        $content = $this->transpileElseIf($content, $data);
        $content = $this->transpileElse($content, $data);
        $content = $this->transpileEndIf($content, $data);
        $content = $this->transpileForeach($content, $data);
        $content = $this->transpileEndForeach($content, $data);

        return $content;
    }

    private function transpileIncludes(string $content, array $data = []): string
    {
        $pattern = '/<include\s+path\s*=\s*["\']([^""]*)["\']\s*>/';
        $content = preg_replace_callback($pattern, function ($matches) use ($data) {
            $includePath = PathResolver::getViewPath() . '/' . $matches[1] . '.html';

            // Get the content of the included file
            $includedContent = File::readFile($includePath);

            // Transpile the included content
            $includedContent = $this->transpileElementsWithVarAttr($includedContent, $data);
            $includedContent = $this->transpileIf($includedContent, $data);
            $includedContent = $this->transpileElseIf($includedContent, $data);
            $includedContent = $this->transpileElse($includedContent, $data);
            $includedContent = $this->transpileEndIf($includedContent, $data);
            $includedContent = $this->transpileForeach($includedContent, $data);
            $includedContent = $this->transpileEndForeach($includedContent, $data);

            // Replace the original <include> tag with the transpiled content
            return $includedContent;
        }, $content);

        return $content;
    }


    private function transpileElementsWithVarAttr(string $content, array $data = []): string
    {
        $pattern = '/<([a-zA-Z0-9_-]+)\s+([^>]*\s+)?var\s*=\s*["\']([^"\']*)["\']([^>]*)>(.*?)<\/\1>/s';
        $content = preg_replace_callback($pattern, function ($matches) {
            // $matches[0] contains the entire match
            // $matches[1] contains the tag name
            // $matches[3] contains the value of the var attribute
            $attributes = !empty($matches[2]) ? $matches[2] : $matches[4];
            if (str_contains($matches[3], '->')) {
                $array = explode('->', $matches[3]);
                $arrayMatches = [];
                foreach ($array as $key => $value) {
                    $value = trim($value);
                    if ($value !== '') {
                        $value = ($key === 0) ? $value : "['" . $value . "']";
                        $arrayMatches[] = $value;
                    }
                }
                $arrayMatches = implode('', $arrayMatches);
            } else {
                $arrayMatches = $matches[3];
            }
            return '<' . $matches[1] . ' ' . $attributes . '><?= $' . $arrayMatches . ' ?></' . $matches[1] . '>';
        }, $content);
        return $content;
    }

    private function transpileIf(string $content, array $data = []): string
    {
        $pattern = '/<if\s+condition\s*=\s*["\']([^""]*)["\']\s*>/';
        $content = preg_replace_callback($pattern, function ($matches) {
            return '<?php if(' . $matches[1] . '): ?>';
        }, $content);
        return $content;
    }

    private function transpileElseIf(string $content, array $data = []): string
    {
        $pattern = '/<elseif\s+condition\s*=\s*["\']([^""]*)["\']\s*>/';
        $content = preg_replace_callback($pattern, function ($matches) {
            return '<?php elseif(' . $matches[1] . '): ?>';
        }, $content);
        return $content;
    }

    private function transpileElse(string $content, array $data = []): string
    {
        $pattern = '/<else\s*>/';
        $content = preg_replace($pattern, '<?php else: ?>', $content);
        return $content;
    }

    private function transpileEndIf(string $content, array $data = []): string
    {
        $pattern = '/<endif\s*>/';
        $content = preg_replace($pattern, '<?php endif; ?>', $content);
        return $content;
    }

    private function transpileForeach(string $content, array $data = []): string
    {
        $pattern = '/<foreach\s+source\s*=\s*["\']([^"\']*)["\']\s*(?:alias\s*=\s*["\']([^"\']*)["\'])?\s*>/';
        $content = preg_replace_callback($pattern, function ($matches) {
            // $matches[0] contains the entire match
            // $matches[1] contains the value of the source attribute
            // $matches[2] contains the value of the alias attribute if present
            try {
                if (empty($matches[2])) {
                    throw new HtmplateException('Attribute Not Found', 'The foreach tag must have an alias attribute.', 2002);
                }
                if (empty($matches[1])) {
                    throw new HtmplateException('Attribute Not Found', 'The foreach tag must have a source attribute.', 2002);
                }
                $aliasArray = explode('=>', $matches[2]);
                $aliases = [];
                foreach ($aliasArray as $alias) {
                    $aliases[] = trim($alias);
                }
                if (str_contains($matches[1], '->')) {
                    $array = explode('->', $matches[1]);
                    $arrayMatches = [];
                    foreach ($array as $key => $value) {
                        $value = trim($value);
                        if ($value !== '') {
                            $value = ($key === 0) ? $value : "['" . $value . "']";
                            $arrayMatches[] = $value;
                        }
                    }
                    $arrayMatches = implode('', $arrayMatches);
                } else {
                    $arrayMatches = $matches[1];
                }
                if (count($aliases) == 1) {
                    return '<?php foreach($' . $arrayMatches . ' as $' . $aliases[0] . '): ?>';
                } elseif (count($aliases) == 2) {
                    return '<?php foreach($' . $arrayMatches . ' as $' . $aliases[0] . ' => $' . $aliases[1] . '): ?>';
                } else {
                    throw new HtmplateException('Invalid Attribute Value', 'The foreach tag must have a value of value or key => value only.', 2003);
                }
            } catch (HtmplateException $e) {
                $e->render(true);
            }
        }, $content);

        return $content;
    }

    private function transpileEndForeach(string $content, array $data = []): string
    {
        $pattern = '/<endforeach\s*>/';
        $content = preg_replace($pattern, '<?php endforeach; ?>', $content);
        return $content;
    }
}
