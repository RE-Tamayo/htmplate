<?php

namespace Retamayo\Htmplate\Exceptions;

use Exception, Throwable;
use Retamayo\Htmplate\Interfaces\CustomException;
use Retamayo\Htmplate\Classes\PathResolver;

class HtmplateException extends Exception implements CustomException
{
    private string $path;
    private string $title;

    public function __construct($title = 'Error', $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->path = PathResolver::getLibPath() . '/Exceptions/responses/response.html';
        $this->title = $title;
    }

    public function render($isClosure = false)
    {
        $data = [
            'title' => $this->title,
            'message' => self::getMessage(),
            'code' => self::getCode(),
            'trace' => self::getTrace(),
            'isClosure' => $isClosure,
        ];
        ob_start();
        extract($data);
        include $this->path;
        echo ob_get_clean();
        die();
    }
}
