<?php
/**
 * Created by PhpStorm.
 * User: les.peabody
 * Date: 10/13/15
 * Time: 10:44 PM
 */

namespace Skills\FactorGraphs;

class ScheduleLoop extends Schedule
{
  private $_maxDelta;
  private $_scheduleToLoop;

  public function __construct($name, Schedule $scheduleToLoop, $maxDelta)
  {
    parent::__construct($name);
    $this->_scheduleToLoop = $scheduleToLoop;
    $this->_maxDelta = $maxDelta;
  }

  public function visit($depth = -1, $maxDepth = 0)
  {
    $totalIterations = 1;
    $delta = $this->_scheduleToLoop->visit($depth + 1, $maxDepth);
    while ($delta > $this->_maxDelta)
    {
      $delta = $this->_scheduleToLoop->visit($depth + 1, $maxDepth);
      $totalIterations++;
    }

    return $delta;
  }
}
