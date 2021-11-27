<?php

namespace shamanzpua\Profiler\Contracts;

interface ICustomProfiler
{
    public function init();
    public function run() : array;
}