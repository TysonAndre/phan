<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language;

use Phan\CodeBase\File;
use Phan\Config;
/**
 * An object representing the context in which any
 * structural element (such as a class or method) lives.
 */
class FileRef implements \Serializable
{
    /**
     * @var string
     * The path to the file in which this element is defined
     */
    protected $file = 'internal';
    /**
     * @var int
     * The starting line number of the element within the $file
     */
    protected $line_number_start = 0;
    /**
     * @var int
     * The ending line number of the element within the $file
     */
    protected $line_number_end = 0;
    /**
     * @param string $file
     * The path to the file in which this element is defined
     *
     * @return static
     * This context with the given value is returned
     */
    public function withFile($file)
    {
        if (!is_string($file)) {
            throw new \InvalidArgumentException("Argument \$file passed to withFile() must be of the type string, " . (gettype($file) == "object" ? get_class($file) : gettype($file)) . " given");
        }
        $context = clone $this;
        $context->file = $file;
        return $context;
    }
    /**
     * @return string
     * The path to the file in which the element is defined
     */
    public function getFile()
    {
        $ret5902c6f6362f5 = $this->file;
        if (!is_string($ret5902c6f6362f5)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6362f5) . " given");
        }
        return $ret5902c6f6362f5;
    }
    /**
     * @return string
     * The path of the file relative to the project
     * root directory
     */
    public function getProjectRelativePath()
    {
        $ret5902c6f6365bc = self::getProjectRelativePathForPath($this->file);
        if (!is_string($ret5902c6f6365bc)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6365bc) . " given");
        }
        return $ret5902c6f6365bc;
    }
    /**
     * @param string $cwd_relative_path (relative or absolute path)
     * @return string
     * The path of the file relative to the project
     * root directory
     */
    public static function getProjectRelativePathForPath($cwd_relative_path)
    {
        // Get a path relative to the project root
        $path = str_replace(Config::get()->getProjectRootDirectory(), '', realpath($cwd_relative_path) ?: $cwd_relative_path);
        // Strip any beginning directory separators
        if (0 === ($pos = strpos($path, DIRECTORY_SEPARATOR))) {
            $path = substr($path, $pos + 1);
        }
        return $path;
    }
    /**
     * @return bool
     * True if this object is internal to PHP
     */
    public function isPHPInternal()
    {
        $ret5902c6f6369aa = 'internal' === $this->getFile();
        if (!is_bool($ret5902c6f6369aa)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f6369aa) . " given");
        }
        return $ret5902c6f6369aa;
    }
    /**
     * @var int $line_number
     * The starting line number of the element within the file
     *
     * @return static
     * This context with the given value is returned
     */
    public function withLineNumberStart($line_number)
    {
        if (!is_int($line_number)) {
            throw new \InvalidArgumentException("Argument \$line_number passed to withLineNumberStart() must be of the type int, " . (gettype($line_number) == "object" ? get_class($line_number) : gettype($line_number)) . " given");
        }
        $this->line_number_start = $line_number;
        return $this;
    }
    /*
     * @return int
     * The starting line number of the element within the file
     */
    public function getLineNumberStart()
    {
        $ret5902c6f636ec1 = $this->line_number_start;
        if (!is_int($ret5902c6f636ec1)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f636ec1) . " given");
        }
        return $ret5902c6f636ec1;
    }
    /**
     * @param int $line_number
     * The ending line number of the element within the $file
     *
     * @return static
     * This context with the given value is returned
     */
    public function withLineNumberEnd($line_number)
    {
        if (!is_int($line_number)) {
            throw new \InvalidArgumentException("Argument \$line_number passed to withLineNumberEnd() must be of the type int, " . (gettype($line_number) == "object" ? get_class($line_number) : gettype($line_number)) . " given");
        }
        $this->line_number_end = $line_number;
        return $this;
    }
    /**
     * Get a string representation of the context
     *
     * @return string
     */
    public function __toString()
    {
        $ret5902c6f6373ea = $this->file . ':' . $this->line_number_start;
        if (!is_string($ret5902c6f6373ea)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6373ea) . " given");
        }
        return $ret5902c6f6373ea;
    }
    public function serialize()
    {
        return (string) $this;
    }
    public function unserialize($serialized)
    {
        $map = explode(':', $serialized);
        $this->file = $map[0];
        $this->line_number_start = (int) $map[1];
        $this->line_number_end = (int) call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$map[2], @0);
    }
}