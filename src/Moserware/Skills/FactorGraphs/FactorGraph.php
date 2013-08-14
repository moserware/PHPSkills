<?php
namespace Moserware\Skills\FactorGraphs;

require_once(dirname(__FILE__) . "/VariableFactory.php");

class FactorGraph
{
    private $_variableFactory;

    public function getVariableFactory()
    {
        $factory = $this->_variableFactory;
        return $factory;
    }

    public function setVariableFactory(VariableFactory $factory)
    {
        $this->_variableFactory = $factory;
    }
}
?>
