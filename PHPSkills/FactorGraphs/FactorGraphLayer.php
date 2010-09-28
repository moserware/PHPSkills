<?php
namespace Moserware\Skills\FactorGraphs;

require_once(dirname(__FILE__) . "/FactorGraph.php");
require_once(dirname(__FILE__) . "/Schedule.php");

abstract class FactorGraphLayer
{
    private $_localFactors = array();
    private $_outputVariablesGroups = array();    
    private $_inputVariablesGroups = array();
    private $_parentFactorGraph;

    protected function __construct(FactorGraph &$parentGraph)
    {
        $this->_parentFactorGraph = &$parentGraph;
    }

    protected function &getInputVariablesGroups()
    {
        return $this->_inputVariablesGroups;        
    }

    // HACK

    public function &getParentFactorGraph()
    {
        return $this->_parentFactorGraph;
    }

    public function &getOutputVariablesGroups()
    {
        return $this->_outputVariablesGroups;        
    }

    public function &getLocalFactors()
    {
        return $this->_localFactors;        
    }

    public function setInputVariablesGroups(&$value)
    {
        $this->_inputVariablesGroups = $value;
    }

    protected function scheduleSequence(&$itemsToSequence, $name)
    {
        return new ScheduleSequence($name, $itemsToSequence);
    }

    protected function addLayerFactor(&$factor)
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
