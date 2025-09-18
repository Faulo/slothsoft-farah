<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Tracking;

use Slothsoft\Core\ServerEnvironment;

class Tick {
    
    protected static $logTick;
    
    public static function log() {
        if (! self::$logTick) {
            self::$logTick = new Tick();
        }
        self::$logTick->append();
    }
    
    protected $functionList;
    
    protected $functionFile;
    
    protected $lastFunction;
    
    protected $timelineList;
    
    protected $timelineFile;
    
    protected $lastTimeline;
    
    protected $lastTime;
    
    protected function __construct() {
        $this->create();
    }
    
    public function __destruct() {
        $this->save();
    }
    
    protected function create() {
        $this->lastTime = get_execution_time();
        
        $this->functionList = [];
        $this->functionFile = ServerEnvironment::getLogDirectory() . 'tracking.tick.function.log';
        file_put_contents($this->functionFile, '');
        
        $this->timelineList = [];
        $this->timelineFile = ServerEnvironment::getLogDirectory() . 'tracking.tick.timeline.log';
        file_put_contents($this->timelineFile, '');
    }
    
    protected function save() {
        $arr = $this->functionList;
        arsort($arr);
        foreach ($arr as $key => &$val) {
            $val = sprintf('% 4d: %s', $val, $key);
        }
        unset($val);
        file_put_contents($this->functionFile, implode(PHP_EOL, $arr));
        file_put_contents($this->timelineFile, implode(PHP_EOL, $this->timelineList));
    }
    
    protected function append() {
        $mode = true;
        if ($mode) {
            $time = get_execution_time();
            $backtraceList = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $backtrace = array_pop($backtraceList);
            $func = isset($backtrace['class'], $backtrace['type']) ? $backtrace['class'] . $backtrace['type'] . $backtrace['function'] : $backtrace['function'];
            $this->timelineList[] = sprintf('%6dms (+%4dms) & %5dMB to get to %s', $time, $time - $this->lastTime, memory_get_peak_usage() / 1048576, $func);
            $this->lastTime = $time;
        } else {
            $backtraceList = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($backtraceList);
            array_shift($backtraceList);
            
            $funcList = [];
            foreach ($backtraceList as $backtrace) {
                $func = isset($backtrace['class'], $backtrace['type']) ? $backtrace['class'] . $backtrace['type'] . $backtrace['function'] : $backtrace['function'];
                if (! isset($this->functionList[$func])) {
                    $this->functionList[$func] = 0;
                }
                $this->functionList[$func] ++;
                $funcList[] = $func . '#' . $backtrace['line'];
            }
            $val = implode('|', array_reverse($funcList));
            if ($this->lastTimeline !== $val) {
                $this->lastTimeline = $val;
                $this->timelineList[] = $val;
                $this->timelineList[] = print_execution_time(false);
            }
        }
        // $this->save();
    }
}