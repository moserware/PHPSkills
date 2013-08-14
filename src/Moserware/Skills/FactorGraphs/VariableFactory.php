<?php

namespace Moserware\Skills\FactorGraphs;

require_once(dirname(__FILE__) . "/Variable.php");

class VariableFactory
{
    // using a Func<TValue> to encourage fresh copies in case it's overwritten
    private $_variablePriorInitializer;

    public function __construct($variablePriorInitializer)
    {
        $this->_variablePriorInitializer = $variablePriorInitializer;
    }

    public function createBasicVariable($name)
    {
        $initializer = $this->_variablePriorInitializer;
        $newVar = new Variable($name, $initializer());
        return $newVar;
    }

    public function createKeyedVariable($key, $name)
    {
        $initializer = $this->_variablePriorInitializer;
        $newVar = new KeyedVariable($key, $name, $initializer());
        return $newVar;
    }
}

?>
