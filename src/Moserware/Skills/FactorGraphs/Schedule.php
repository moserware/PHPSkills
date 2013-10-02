<?php
namespace Moserware\Skills\FactorGraphs;

abstract class Schedule
{
    private $_name;

    protected function __construct($name)
    {
        $this->_name = $name;
    }

    public abstract function visit($depth = -1, $maxDepth = 0);

    public function __toString()
    {
        return $this->_name;
    }
}