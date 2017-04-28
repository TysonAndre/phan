<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan;

use Phan\Output\Colorizing;
class IssueInstance
{
    /** @var Issue */
    private $issue;
    /** @var string */
    private $file;
    /** @var int */
    private $line;
    /** @var string */
    private $message;
    /**
     * @param Issue $issue
     * @param string $file
     * @param int $line
     * @param array $template_parameters
     */
    public function __construct(Issue $issue, $file, $line, array $template_parameters)
    {
        if (!is_string($file)) {
            throw new \InvalidArgumentException("Argument \$file passed to __construct() must be of the type string, " . (gettype($file) == "object" ? get_class($file) : gettype($file)) . " given");
        }
        if (!is_int($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to __construct() must be of the type int, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        $this->issue = $issue;
        $this->file = $file;
        $this->line = $line;
        // color_issue_message will interfere with some formatters, such as xml.
        if (Config::get()->color_issue_messages) {
            $this->message = self::generateColorizedMessage($issue, $template_parameters);
        } else {
            $this->message = self::generatePlainMessage($issue, $template_parameters);
        }
    }
    private static function generatePlainMessage(Issue $issue, array $template_parameters)
    {
        $template = $issue->getTemplate();
        // markdown_issue_messages doesn't make sense with color, unless you add <span style="color:red">msg</span>
        // Not sure if codeclimate supports that.
        if (Config::get()->markdown_issue_messages) {
            $template = preg_replace('/([^ ]*%s[^ ]*)/', '`\\1`', $template);
        }
        $ret5902c6f478643 = vsprintf($template, $template_parameters);
        if (!is_string($ret5902c6f478643)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f478643) . " given");
        }
        return $ret5902c6f478643;
    }
    private static function generateColorizedMessage(Issue $issue, array $template_parameters)
    {
        $template = $issue->getTemplateRaw();
        $ret5902c6f478906 = Colorizing::colorizeTemplate($template, $template_parameters);
        if (!is_string($ret5902c6f478906)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f478906) . " given");
        }
        return $ret5902c6f478906;
    }
    /**
     * @return Issue
     */
    public function getIssue()
    {
        $ret5902c6f478b98 = $this->issue;
        if (!$ret5902c6f478b98 instanceof Issue) {
            throw new \InvalidArgumentException("Argument returned must be of the type Issue, " . (gettype($ret5902c6f478b98) == "object" ? get_class($ret5902c6f478b98) : gettype($ret5902c6f478b98)) . " given");
        }
        return $ret5902c6f478b98;
    }
    /**
     * @return string
     */
    public function getFile()
    {
        $ret5902c6f478ebe = $this->file;
        if (!is_string($ret5902c6f478ebe)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f478ebe) . " given");
        }
        return $ret5902c6f478ebe;
    }
    /**
     * @return int
     */
    public function getLine()
    {
        $ret5902c6f479117 = $this->line;
        if (!is_int($ret5902c6f479117)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f479117) . " given");
        }
        return $ret5902c6f479117;
    }
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    public function __toString()
    {
        $ret5902c6f4793db = "{$this->getFile()}:{$this->getLine()} {$this->getMessage()}";
        if (!is_string($ret5902c6f4793db)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f4793db) . " given");
        }
        return $ret5902c6f4793db;
    }
}