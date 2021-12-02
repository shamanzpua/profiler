<?php
use shamanzpua\Profiler\Profiler;

function performance_profiling_start(string $name)
{
    profiler_breakpoint(['name' => $name]);
}

function performance_profiling_stop($breakPoint = null)
{
    $profiler = Profiler::getInstance();
    $profiler->setMissingTraceReason(Profiler::REASON_PROFILING_END);
    profiler_breakpoint($breakPoint);
    $profiler->disableDestructBreakPoint();
    $logStorage = $profiler->getLogStorage();
    Profiler::unset();
    unset($profiler);
    Profiler::getInstance()->setLogStorage($logStorage);
}

function profiler_breakpoint($metadata = null)
{
    $profiler = Profiler::getInstance();

    if (is_array($metadata) && isset($metadata['name'])) {
        $profiler->setName($metadata['name']);
        $profiler->initCustomProfilers();
    }

    $profiler->breakpoint(is_string($metadata) ? $metadata : null);
}