<?php
namespace Moserware\Skills\FactorGraphs;

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