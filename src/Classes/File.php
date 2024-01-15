<?php

namespace Retamayo\Htmplate\Classes;

use Retamayo\Htmplate\Exceptions\HtmplateException;

class File
{
    public static function createPath($path): void
    {
        try {
            if (!file_exists($path)) {
                if(!mkdir($path, 0777, true)) {
                    throw new HtmplateException('Unable To Write', 'Unable to create the directory, please check script permissions', 1002);
                }
            }
        } catch (HtmplateException $e) {
            $e->render();
        }
    }

    public static function createFile($path): void
    {
        try {
            if (!file_exists($path)) {
                if(!touch($path)) {
                    throw new HtmplateException('Unable To Write', 'Unable to create the file, please check script permissions', 1002);
                }
            }
        } catch (HtmplateException $e) {
            $e->render();
        }
    }

    public static function deleteFile($path): void
    {
        try {
            if (!file_exists($path)) {
                throw new HtmplateException('File Not Found', 'The file you are trying to delete does not exist', 1001);
            }
            if (!unlink($path)) {
                throw new HtmplateException('File Error', 'There was an error while processing the file', 1004);
            }    
        } catch (HtmplateException $e) {
            $e->render();
        }
    }

    public static function readFile($path): string
    {
        try {
            if (!file_exists($path)) {
                throw new HtmplateException("File Not Found", "The file you are trying to read does not exist", 1001);
            }
            if (!is_readable($path)) {
                throw new HtmplateException("Unable To Read", "The file you are trying to read is not readable", 1003);
            }
            return file_get_contents($path);
        } catch (HtmplateException $e) {
            $e->render();
        }
    }

    public static function writeFile($path, $content): void
    {
        try {
            if (!file_exists($path)) {
                throw new HtmplateException("File Not Found", "The file you are trying to write does not exist", 1001);
            }
            if (is_dir($path)) { 
                throw new HtmplateException("Unable To Write", "The file you are trying to write is a directory", 1002);
            }
            if (!is_writable($path)) {
                throw new HtmplateException("Unable To Write", "The file you are trying to write is not writable", 1002);
            }
            if (!file_put_contents($path, $content)) {
                throw new HtmplateException("File Error", "There was an error while processing the file", 1004);
            }
        } catch (HtmplateException $e) {
            $e->render();
        }
    }
}
