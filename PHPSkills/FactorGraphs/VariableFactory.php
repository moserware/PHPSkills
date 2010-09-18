<?php

namespace Moserware\Skills\FactorGraphs;

class VariableFactory
{
    // using a Func<TValue> to encourage fresh copies in case it's overwritten
    private $_variablePriorInitializer;

    public function __construct($variablePriorInitializer)
    {
        $this->_variablePriorInitializer = $variablePriorInitializer;
    }

    public function createBasicVariable()
    {
        $newVar = new Variable($this->_variablePriorInitializer());
        return $newVar;
    }

    public function createKeyedVariable($key)
    {
        $newVar = new KeyedVariable($key, $this->_variablePriorInitializer());
        return $newVar;
    }
}s

?>
