<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan;

use ast\Node;
class Prep
{
    /**
     * Scan a list of files, applying the given closure to every
     * AST node
     *
     * @param string[] $file_list
     * A list of files to scan
     *
     * @param \Closure $visit_node
     * A closure that is to be applied to every AST node
     *
     * @return void
     */
    public static function scanFileList(array $file_list, \Closure $visit_node)
    {
        foreach ($file_list as $file_path) {
            // Convert the file to an Abstract Syntax Tree
            // before passing it on to the recursive version
            // of this method
            $node = \ast\parse_file($file_path, Config::get()->ast_version);
            // Skip empty files
            if (!$node) {
                continue;
            }
            self::scanNodeInFile($node, $file_path, $visit_node);
        }
    }
    /**
     * Recursively scan a node and its children applying the
     * given closure to each.
     *
     * @param string $file_path
     * The file in which the node exists
     *
     * @param \Closure $visit_node
     * A closure that is to be applied to every AST node
     *
     * @return void
     */
    public static function scanNodeInFile(Node $node, $file_path, \Closure $visit_node)
    {
        if (!is_string($file_path)) {
            throw new \InvalidArgumentException("Argument \$file_path passed to scanNodeInFile() must be of the type string, " . (gettype($file_path) == "object" ? get_class($file_path) : gettype($file_path)) . " given");
        }
        // Visit the node doing whatever the caller
        // likes
        $visit_node($node, $file_path);
        // Scan all children recursively
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $child_node) {
            if (!$child_node instanceof Node) {
                continue;
            }
            self::scanNodeInFile($child_node, $file_path, $visit_node);
        }
    }
}