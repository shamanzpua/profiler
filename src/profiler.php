<?php
use shamanzpua\Profiler\Profiler;

function profileStart(string $name)
{
    profile(['name' => $name]);
}

function profileEnd($breakPoint = null)
{
    profile($breakPoint);
    $profiler = Profiler::getInstance();
    $profiler->disableDestructBreakPoint();
    unset($profiler);
}

function profile($metadata = null)
{
    $profiler = Profiler::getInstance();

    if (is_array($metadata) && isset($metadata['name'])) {
        $profiler->setName($metadata['name']);
        $profiler->initCustomProfilers();
    }

    $profiler->breakpoint(is_string($metadata) ? $metadata : null);
}