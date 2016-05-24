<?php namespace Moserware\Skills\FactorGraphs;

class FactorGraph
{
    private $_variableFactory;

    public function getVariableFactory()
    {
        return $this->_variableFactory;
    }

    public function setVariableFactory(VariableFactory $factory)
    {
        $this->_variableFactory = $factory;
    }
}