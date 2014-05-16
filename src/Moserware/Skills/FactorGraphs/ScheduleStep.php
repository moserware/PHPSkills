<?php
namespace Moserware\Skills\FactorGraphs;

class ScheduleStep extends Schedule
{
    private $_factor;
    private $_index;

    public function __construct($name, Factor $factor, $index)
    {
        parent::__construct($name);
        $this->_factor = $factor;
        $this->_index = $index;
    }

    public function visit($depth = -1, $maxDepth = 0)
    {
        $currentFactor = $this->_factor;
        $delta = $currentFactor->updateMessageIndex($this->_index);
        return $delta;
    }
}