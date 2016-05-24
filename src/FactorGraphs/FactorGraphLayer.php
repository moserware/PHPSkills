<?php namespace Moserware\Skills\FactorGraphs;
// edit this
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
        return $this->_inputVariablesGroups;
    }

    // HACK

    public function getParentFactorGraph()
    {
        return $this->_parentFactorGraph;
    }

    /**
     * This reference is still needed
     *
     * @return array
     */
    public function &getOutputVariablesGroups()
    {
        return $this->_outputVariablesGroups;
    }

    public function getLocalFactors()
    {
        return $this->_localFactors;
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