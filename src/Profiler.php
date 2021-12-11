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
    private $logDurationThreshold = 0;

    /**
     * @var ILogStorage $logStorage
     */
    private $logStorage;

    /**
     * @var ICustomProfiler[] $customProfilers
     */
    private $customProfilers = [];

    protected $missingTraceParamReason;

    const REASON_UNKNOWN = 'UNKNOWN';
    const REASON_PROFILING_END = 'PROFILING END';
    const REASON_PROFILING_START = 'PROFILING START';

    /**
     * @var Profiler|null $instance
     */
    private static $instance = null;

    public function __construct()
    {
        $this->logStorage = new NullStorage();
        $this->setMissingTraceReason(static::REASON_PROFILING_START);
    }

    public static function getInstance() : Profiler
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function unset()
    {
        self::$instance = null;
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

    /**
     * @param int $value
     * @return void
     */
    public function setLogDurationThreshold(int $value) : Profiler
    {
        $this->logDurationThreshold = $value;
        return $this;
    }

    /**
     * @return void
     */
    public function getLogDurationThreshold() : int
    {
        return $this->logDurationThreshold;
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
     * @return ILogStorage
     */
    public function getLogStorage() : ILogStorage
    {
        return $this->logStorage;
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
     * @return array
     */
    public function getCustomProfilers() : array
    {
        return $this->customProfilers;
    }

    /**
     * @return mixed
     */
    protected function getMissingTraceReason()
    {
        return $this->missingTraceParamReason;
    }

    /**
     * @return bool
     */
    protected function isKnownMissingTraceReason() : bool
    {
        return $this->missingTraceParamReason !== static::REASON_UNKNOWN;
    }

    public function setMissingTraceReason($reason = self::REASON_UNKNOWN)
    {
        $this->missingTraceParamReason = $reason;
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



        $class = $trace[2]['class'] ?? false;

        $this->currentPoint = [
            'time' => $time,
            'class' => $class ?: $this->getMissingTraceReason(),
            'method' => $trace[2]['function'] ?? $this->getMissingTraceReason(),
            'line' => $trace[1]['line'] ?? $this->getMissingTraceReason(),
            'break_point' => $breakPointName
        ];

        if (!$class && $this->isKnownMissingTraceReason()) {
            $this->currentPoint['specialBreakpoint'] = $this->getMissingTraceReason();
        }


        $this->currentPoint['duration'] =  $this->prevTime ? $this->getDuration($time, $this->prevTime) : 0;

        $this->runCustomProfilers();

        $this->prevTime = $time;
        $this->points[] = $this->currentPoint;
        $this->setMissingTraceReason();
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
            $this->setMissingTraceReason(static::REASON_PROFILING_END);
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

        if ($totalDuration >= $this->logDurationThreshold) {
            $this->logStorage->put($name, $profileResult, $this->startTime);
        }
    }
}
