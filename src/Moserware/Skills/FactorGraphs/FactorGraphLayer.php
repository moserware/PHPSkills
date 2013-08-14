<?php
namespace Moserware\Skills\FactorGraphs;

require_once(dirname(__FILE__) . "/Factor.php");
require_once(dirname(__FILE__) . "/FactorGraph.php");
require_once(dirname(__FILE__) . "/Schedule.php");

abstract class FactorGraphLayer
{
    private $_localFactors = array();
    private $_outputVariablesGroups = array();    
    private $_inputVariablesGroups = array();
    private $_parentFactorGraph;

    protected function __construct(FactorGraph $parentGraph)
    {
        $this->_parentFactorGraph = $parentGraph;
    }

    protected function getInputVariablesGroups()
    {
        $inputVariablesGroups = $this->_inputVariablesGroups;
        return $inputVariablesGroups;
    }

    // HACK

    public function getParentFactorGraph()
    {
        $parentFactorGraph = $this->_parentFactorGraph;
        return $parentFactorGraph;
    }

    public function getOutputVariablesGroups()
    {
        $outputVariablesGroups = $this->_outputVariablesGroups;
        return $outputVariablesGroups;
    }

    public function getLocalFactors()
    {
        $localFactors = $this->_localFactors;
        return $localFactors;
    }

    public function setInputVariablesGroups($value)
    {
        $this->_inputVariablesGroups = $value;
    }

    protected function scheduleSequence(array $itemsToSequence, $name)
    {
        return new ScheduleSequence($name, $itemsToSequence);
    }

    protected function addLayerFactor(Factor $factor)
    {
        $this->_localFactors[] = $factor;
    }

    public abstract function buildLayer();

    public function createPriorSchedule()
    {
        return null;
    }

    public function createPosteriorSchedule()
    {
        return null;
    }
}

?>
