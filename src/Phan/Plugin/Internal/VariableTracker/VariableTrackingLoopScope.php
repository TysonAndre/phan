<?php declare(strict_types=1);
namespace Phan\Plugin\Internal\VariableTracker;

/**
 * This is the same as VariableTrackingBranchScope, but records a level for break/continue.
 */
final class VariableTrackingLoopScope extends VariableTrackingBranchScope
{
    /**
     * @var array<int,VariableTrackingBranchScope>
     * The scopes that broke out early within the inner body of this loop
     */
    public $skipped_loop_scopes = [];

    /**
     * @var array<int,VariableTrackingBranchScope>
     * The scopes that broke out early within the inner body of this loop
     */
    public $skipped_exiting_loop_scopes = [];

    // inherits defs, uses

    // inherit VariableTrackingBranchScope::__construct()

    /**
     * Record a statement that was unreachable due to break/continue statements.
     *
     * @param VariableTrackingBranchScope $skipped_loop_scope
     * @param bool $exits
     * @return void
     */
    public function recordSkippedScope(VariableTrackingBranchScope $skipped_loop_scope, bool $exits)
    {
        if ($exits) {
            $this->skipped_exiting_loop_scopes[] = $skipped_loop_scope;
        } else {
            $this->skipped_loop_scopes[] = $skipped_loop_scope;
        }
        // Subclasses will implement this
    }

    /**
     * @return void
     */
    public function flattenSwitchCaseScopes(VariableGraph $graph)
    {
        foreach ($this->skipped_loop_scopes as $alternate_scope) {
            $this->flattenScopeToMergedLoopResult($this, $alternate_scope, $graph);
        }
        foreach ($this->skipped_exiting_loop_scopes as $alternate_scope) {
            $this->flattenUsesFromScopeToMergedLoopResult($this, $alternate_scope, $graph);
        }
        $this->skipped_loop_scopes = [];
        $this->skipped_exiting_loop_scopes = [];
    }
}
