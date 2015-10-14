<?php
/**
 * Created by PhpStorm.
 * User: les.peabody
 * Date: 10/13/15
 * Time: 10:43 PM
 */

namespace Skills\FactorGraphs;

class ScheduleSequence extends Schedule
{
  private $_schedules;

  public function __construct($name, array $schedules)
  {
    parent::__construct($name);
    $this->_schedules = $schedules;
  }

  public function visit($depth = -1, $maxDepth = 0)
  {
    $maxDelta = 0;

    $schedules = $this->_schedules;
    foreach ($schedules as $currentSchedule)
    {
      $currentVisit = $currentSchedule->visit($depth + 1, $maxDepth);
      $maxDelta = max($currentVisit, $maxDelta);
    }

    return $maxDelta;
  }
}
