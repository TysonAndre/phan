<?php

class InvokeExecutionPromiseSCCP
{
    /** @var string path to the php binary invoked */
    private $binary;

    /** @var bool */
    private $done = false;

    /** @var resource */
    private $process;

    /** @var array{0:resource,1:resource,2:resource} */
    private $pipes;

    /** @var ?string */
    private $error = null;

    /** @var ?string */
    private $output = null;

    /** @var string */
    private $raw_stdout = '';

    /** @var string */
    private $abs_path;

    public function __construct(string $binary, string $file_contents, string $abs_path, string $optimization_level)
    {
        // TODO: Use symfony process
        // Note: We might have invalid utf-8, ensure that the streams are opened in binary mode.
        // I'm not sure if this is necessary.

        $cmd = $binary . " -d opcache.enable_cli=1 -d opcache.opt_debug_level=0x20000 -d opcache.optimization_level=$optimization_level --syntax-check";

        if (DIRECTORY_SEPARATOR === "\\") {

            if (!function_exists('opcache_get_status')) {
                $extension = 'opcache.dll';
                $cmd .= " -d zend_extension=$extension";
            }
            if (!file_exists($abs_path)) {
                $this->done = true;
                $this->error = "File does not exist";
                return;
            }

            // Possibly https://bugs.php.net/bug.php?id=51800
            // NOTE: Work around this by writing from the original file. This may not work as expected in LSP mode
            $abs_path = str_replace("/", "\\", $abs_path);

            $cmd .= ' < ' . escapeshellarg($abs_path);

            $descriptorspec = [
                1 => ['pipe', 'wb'],
            ];
            $this->binary = $binary;
            $process = proc_open($cmd, $descriptorspec, $pipes);
            if (!is_resource($process)) {
                $this->done = true;
                $this->error = "Failed to run proc_open in " . __METHOD__;
                return;
            }
            $this->process = $process;
        } else {
            if (!function_exists('opcache_get_status')) {
                $extension = 'opcache.so';
                $cmd .= " -d zend_extension=$extension";
            }
            echo "Invoking $cmd\n";
            $descriptorspec = [
                ['pipe', 'rb'],
                ['pipe', 'wb'],
            ];
            $this->binary = $binary;
            $process = proc_open($cmd, $descriptorspec, $pipes);
            if (!is_resource($process)) {
                $this->done = true;
                $this->error = "Failed to run proc_open in " . __METHOD__;
                return;
            }
            $this->process = $process;

            self::streamPutContents($pipes[0], $file_contents);
        }
        $this->pipes = $pipes;

        if (!stream_set_blocking($pipes[1], false)) {
            $this->error = "unable to set read stdout to non-blocking";
        }
        $this->abs_path = $abs_path;
    }

    /**
     * @param resource $stream stream to write $file_contents to before fclose()
     * @param string $file_contents
     * @return void
     * See https://bugs.php.net/bug.php?id=39598
     */
    private static function streamPutContents($stream, string $file_contents)
    {
        try {
            while (strlen($file_contents) > 0) {
                $bytes_written = fwrite($stream, $file_contents);
                if ($bytes_written === false) {
                    error_log('failed to write in ' . __METHOD__);
                    return;
                }
                if ($bytes_written === 0) {
                    $read_streams = [];
                    $write_streams = [$stream];
                    $except_streams = [];
                    stream_select($read_streams, $write_streams, $except_streams, 0);
                    if (!$write_streams) {
                        usleep(1000);
                        // This is blocked?
                        continue;
                    }
                    // $stream is ready to be written to?
                    $bytes_written = fwrite($stream, $file_contents);
                    if (!$bytes_written) {
                        error_log('failed to write in ' . __METHOD__ . ' but the stream should be ready');
                        return;
                    }
                }
                if ($bytes_written > 0) {
                    $file_contents = \substr($file_contents, $bytes_written);
                }
            }
        } finally {
            fclose($stream);
        }
    }

    public function read() : bool
    {
        if ($this->done) {
            return true;
        }
        $stdout = $this->pipes[1];
        while (!feof($stdout)) {
            $bytes = fread($stdout, 4096);
            if (strlen($bytes) === 0) {
                break;
            }
            $this->raw_stdout .= $bytes;
        }
        if (!feof($stdout)) {
            return false;
        }
        fclose($stdout);

        $this->done = true;

        $exit_code = proc_close($this->process);
        if ($exit_code === 0) {
            $this->output = str_replace("\r", "", trim($this->raw_stdout));
            $this->error = null;
            return true;
        }
        $output = str_replace("\r", "", trim($this->raw_stdout));
        $first_line = explode("\n", $output)[0];
        $this->error = $first_line;
        return true;
    }

    /**
     * @return void
     * @throws Error if reading failed
     */
    public function blockingRead()
    {
        if ($this->done) {
            return;
        }
        if (!stream_set_blocking($this->pipes[1], true)) {
            throw new Error("Unable to make stdout blocking");
        }
        if (!$this->read()) {
            throw new Error("Failed to read");
        }
    }

    /**
     * @return ?string
     * @throws RangeException if this was called before the process finished
     */
    public function getError()
    {
        if (!$this->done) {
            throw new RangeException("Called " . __METHOD__ . " too early");
        }
        return $this->error;
    }

    /**
     * @return ?string
     * @throws RangeException if this was called before the process finished
     */
    public function getOutput()
    {
        if (!$this->done) {
            throw new RangeException("Called " . __METHOD__ . " too early");
        }
        return $this->output;
    }

    public function getAbsPath() : string
    {
        return $this->abs_path;
    }

    public function getBinary() : string
    {
        return $this->binary;
    }
}

class SCCPChecker {
    private $php_file_name;

    public function __construct(string $php_file_name) {
        $this->php_file_name = $php_file_name;
    }

    public function run() {
        $php_file_name = $this->php_file_name;
        if (!file_exists($php_file_name)) {
            fwrite(STDERR, "$php_file_name does not exist");
            exit(2);
        }
        $contents = file_get_contents($php_file_name);
        $unoptimized_opcode_promise = new InvokeExecutionPromiseSCCP(PHP_BINARY, $contents, $php_file_name, '0');
        $optimized_opcode_promise   = new InvokeExecutionPromiseSCCP(PHP_BINARY, $contents, $php_file_name, '-1');

        $unoptimized_opcode_promise->blockingRead();
        $optimized_opcode_promise->blockingRead();
        $err1 = $unoptimized_opcode_promise->getError();
        $err2 = $optimized_opcode_promise->getError();
        if ($err1) {
            fwrite(STDERR, "Saw error\n$err1\n");
            exit(3);
        }
        if ($err2) {
            fwrite(STDERR, "Saw error\n$err1\n");
            exit(3);
        }
        $output1 = $unoptimized_opcode_promise->getOutput();
        $output2 = $optimized_opcode_promise->getOutput();
        echo "Unoptimized:\n$output1\n\n#######################\n\nOptimized:\n$output2\n";
    }

    public static function main() {
        global $argv;
        if (count($argv) !== 2) {
            echo "Usage: $argv[0] path/to/file_to_analyze.php\n";
            exit(1);
        }
        $runner = new SCCPChecker($argv[1]);
        $runner->run();
    }
}

SCCPChecker::main();
