<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan;

/**
 * Program configuration.
 * See `./phan -h` for command line usage, or take a
 * look at \Phan\CLI.php for more details on CLI usage.
 */
class Config
{
    /**
     * @var string|null
     * The root directory of the project. This is used to
     * store canonical path names and find project resources
     */
    private $project_root_directory = null;
    /**
     * Configuration options
     */
    private $configuration = ['file_list' => [], 'directory_list' => [], 'analyzed_file_extensions' => ['php'], 'exclude_file_regex' => '', 'exclude_file_list' => [], 'exclude_analysis_directory_list' => [], 'backward_compatibility_checks' => true, 'parent_constructor_required' => [], 'quick_mode' => false, 'should_visit_all_nodes' => true, 'analyze_signature_compatibility' => true, 'minimum_severity' => 0, 'allow_missing_properties' => false, 'null_casts_as_any_type' => false, 'scalar_implicit_cast' => false, 'ignore_undeclared_variables_in_global_scope' => false, 'check_docblock_signature_return_type_match' => false, 'dead_code_detection' => false, 'dead_code_detection_prefer_false_negative' => true, 'read_magic_property_annotations' => true, 'read_magic_method_annotations' => true, 'read_type_annotations' => true, 'disable_suppression' => false, 'dump_ast' => false, 'dump_signatures_file' => null, 'progress_bar' => false, 'progress_bar_sample_rate' => 0.005, 'processes' => 1, 'ast_version' => 35, 'profiler_enabled' => false, 'suppress_issue_types' => [], 'whitelist_issue_types' => [], 'runkit_superglobals' => [], 'globals_type_map' => [], 'markdown_issue_messages' => false, 'color_issue_messages' => false, 'color_scheme' => [], 'generic_types_enabled' => true, 'randomize_file_order' => false, 'consistent_hashing_file_order' => false, 'daemonize_socket' => false, 'daemonize_tcp_port' => false, 'plugins' => []];
    /**
     * Disallow the constructor to force a singleton
     */
    private function __construct()
    {
    }
    /**
     * @return string
     * Get the root directory of the project that we're
     * scanning
     */
    public function getProjectRootDirectory()
    {
        $ret5902c6f3e7156 = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$this->project_root_directory, @getcwd());
        if (!is_string($ret5902c6f3e7156)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f3e7156) . " given");
        }
        return $ret5902c6f3e7156;
    }
    /**
     * @param string $project_root_directory
     * Set the root directory of the project that we're
     * scanning
     *
     * @return void
     */
    public function setProjectRootDirectory($project_root_directory)
    {
        if (!is_string($project_root_directory)) {
            throw new \InvalidArgumentException("Argument \$project_root_directory passed to setProjectRootDirectory() must be of the type string, " . (gettype($project_root_directory) == "object" ? get_class($project_root_directory) : gettype($project_root_directory)) . " given");
        }
        $this->project_root_directory = $project_root_directory;
    }
    /**
     * @return Config
     * Get a Configuration singleton
     */
    public static function get()
    {
        static $instance;
        if ($instance) {
            $ret5902c6f3e77d1 = $instance;
            if (!$ret5902c6f3e77d1 instanceof Config) {
                throw new \InvalidArgumentException("Argument returned must be of the type Config, " . (gettype($ret5902c6f3e77d1) == "object" ? get_class($ret5902c6f3e77d1) : gettype($ret5902c6f3e77d1)) . " given");
            }
            return $ret5902c6f3e77d1;
        }
        $instance = new Config();
        $ret5902c6f3e7aff = $instance;
        if (!$ret5902c6f3e7aff instanceof Config) {
            throw new \InvalidArgumentException("Argument returned must be of the type Config, " . (gettype($ret5902c6f3e7aff) == "object" ? get_class($ret5902c6f3e7aff) : gettype($ret5902c6f3e7aff)) . " given");
        }
        return $ret5902c6f3e7aff;
    }
    /**
     * @return array
     * A map of configuration keys and their values
     */
    public function toArray()
    {
        $ret5902c6f3e7dd5 = $this->configuration;
        if (!is_array($ret5902c6f3e7dd5)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f3e7dd5) . " given");
        }
        return $ret5902c6f3e7dd5;
    }
    /** @return mixed */
    public function __get($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __get() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        return $this->configuration[$name];
    }
    public function __set($name, $value)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __set() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $this->configuration[$name] = $value;
    }
    /**
     * @return string
     * The relative path appended to the project root directory.
     *
     * @suppress PhanUnreferencedMethod
     */
    public static function projectPath($relative_path)
    {
        if (!is_string($relative_path)) {
            throw new \InvalidArgumentException("Argument \$relative_path passed to projectPath() must be of the type string, " . (gettype($relative_path) == "object" ? get_class($relative_path) : gettype($relative_path)) . " given");
        }
        // Make sure its actually relative
        if (DIRECTORY_SEPARATOR == substr($relative_path, 0, 1)) {
            return $relative_path;
        }
        return implode(DIRECTORY_SEPARATOR, [Config::get()->getProjectRootDirectory(), $relative_path]);
    }
}