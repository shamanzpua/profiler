<?php


namespace shamanzpua\Profiler;


use shamanzpua\Profiler\Contracts\ICustomProfiler;
use shamanzpua\Profiler\Contracts\ILogStorage;
use shamanzpua\Profiler\LogStorages\NullStorage;

class Profiler
{
    private $startTime;
    private $prevTime;
    private $points = [];
    private $currentPoint;
    private $breakPointOnDestruct = true;
    private $name;

    /**
     * @var ILogStorage $logStorage
     */
    private $logStorage;

    /**
     * @var ICustomProfiler[] $customProfilers
     */
    private $customProfilers = [];

    private $missingTraceParamReason = 'UNKNOWN';

    const REASON_PROFILING_END = 'PROFILING END';

    /**
     * @var Profiler|null $instance
     */
    private static $instance = null;

    public function __construct()
    {
        $this->logStorage = new NullStorage();
    }

    public static function getInstance() : Profiler
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function disableDestructBreakPoint()
    {
        $this->breakPointOnDestruct = false;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function initCustomProfilers()
    {
        foreach ($this->customProfilers as $customProfiler) {
            $customProfiler->init();
        }
    }

    /**
     * @param ILogStorage $logStorage
     * @return $this
     */
    public function setLogStorage(ILogStorage $logStorage) : Profiler
    {
        $this->logStorage = $logStorage;
        return $this;
    }

    /**
     * @param ICustomProfiler $customProfiler
     * @return $this
     */
    public function setCustomProfiler(ICustomProfiler $customProfiler) : Profiler
    {
        $this->customProfilers[] = $customProfiler;
        return $this;
    }

    /**
     * @param null $breakPointName
     * @return false|void
     */
    public function breakpoint($breakPointName = null)
    {
        if (!$this->hasName()) {
            return false;
        }

        $time =  (microtime(true));

        if (!$this->startTime) {
            $this->startTime = $time;
        }
        $trace = (new \Exception())->getTrace();

        $this->currentPoint = [
            'time' => $time,
            'class' => $trace[2]['class'] ?? $this->missingTraceParamReason,
            'method' => $trace[2]['function'] ?? $this->missingTraceParamReason,
            'line' => $trace[1]['line'] ?? $this->missingTraceParamReason,
            'break_point' => $breakPointName
        ];

        $this->currentPoint['duration'] =  $this->prevTime ? $this->getDuration($time, $this->prevTime) : 0;

        $this->runCustomProfilers();

        $this->prevTime = $time;
        $this->points[] = $this->currentPoint;
    }

    private function runCustomProfilers()
    {
        foreach ($this->customProfilers as $customProfiler) {
            $this->currentPoint = array_merge($this->currentPoint, $customProfiler->run());
        }
    }



    /**
     * @return bool
     */
    private function hasName() : bool
    {
        return (bool) $this->name;
    }

    private function getDuration($time, $prevTime)
    {
        $duration = round($time - $prevTime, 3);

        return $duration * 1000;
    }

    public function __destruct()
    {
        if (!$this->hasName()) {
            return false;
        }

        if ($this->breakPointOnDestruct) {
            $this->missingTraceParamReason = static::REASON_PROFILING_END;
            $this->breakpoint();
        }

        $this->end();
    }

    private function end()
    {
        $totalDuration = $this->getDuration($this->prevTime, $this->startTime);

        $profileResult = [
            'start_time' => $this->startTime,
            'end_time' => $this->prevTime,
            'total_duration' => $totalDuration,
            'stacktrace' => $this->points
        ];

        $name = $this->name;

        $this->logStorage->put($name, $profileResult, $this->startTime);
    }
}
