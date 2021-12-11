<?php
use shamanzpua\Profiler\Profiler;
use shamanzpua\Profiler\Contracts\ICustomProfiler;

/**
 * @param string $name
 * @param int $logDurationThreshold
 * @return void
 */
function performance_profiling_start(string $name, int $logDurationThreshold = 0)
{
    $metadata = ['name' => $name];
    if ($logDurationThreshold) {
        $metadata['log_duration_threshold'] = $logDurationThreshold;
    }

    profiler_breakpoint($metadata);
}

/**
 * @param $breakPoint
 * @return void
 */
function performance_profiling_stop($breakPoint = null)
{
    $profiler = Profiler::getInstance();
    $profiler->setMissingTraceReason(Profiler::REASON_PROFILING_END);
    profiler_breakpoint($breakPoint);
    $profiler->disableDestructBreakPoint();
    $logStorage = $profiler->getLogStorage();
    $customProfilers = $profiler->getCustomProfilers();
    $logDurationThreshold = $profiler->getLogDurationThreshold();
    Profiler::unset();
    unset($profiler);
    Profiler::getInstance()
        ->setLogStorage($logStorage)
        ->setLogDurationThreshold($logDurationThreshold);
    foreach ($customProfilers as $customProfiler) {
        if ($customProfiler instanceof ICustomProfiler) {
            Profiler::getInstance()->setCustomProfiler($customProfiler);
        }
    }
}

function profiler_breakpoint($metadata = null)
{
    $profiler = Profiler::getInstance();

    if (is_array($metadata)) {
        if (isset($metadata['name'])) {
            $profiler->setName($metadata['name']);
            $profiler->initCustomProfilers();
        }

        if (isset($metadata['log_duration_threshold'])) {
            $profiler->setLogDurationThreshold($metadata['log_duration_threshold']);
        }
    }

    $profiler->breakpoint(is_string($metadata) ? $metadata : null);
}