<?php declare(strict_types=1);
namespace Phan\Request;

use Phan\Output\IssuePrinterInterface;

/**
 * A request to analyze one or more files.
 *
 * Used by Phan daemon, will be used by the open language server implementation.
 *
 * In the future, more types of requests may be added.
 */
interface AnalysisRequest {
    /**
     * Respond with a message indicating that 0 files would be analyzed.
     * @return void
     */
    public function respondWithNoFilesToAnalyze();

    /**
     * Respond with issues in the requested format. (in the way corresponding to that protocol)
     *
     * @return void
     */
    public function respondWithIssues(int $issueCount);

    /**
     * Returns the printer used.
     * Configurable via CLI options.
     * The Phan daemon also allows overriding the 'format' via request parameters.
     */
    public function getPrinter() : IssuePrinterInterface;
}
