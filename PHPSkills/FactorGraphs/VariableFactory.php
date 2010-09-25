<?php

namespace Moserware\Skills\FactorGraphs;

require_once(dirname(__FILE__) . "/Variable.php");

class VariableFactory
{
    // using a Func<TValue> to encourage fresh copies in case it's overwritten
    private $_variablePriorInitializer;

    public function __construct($variablePriorInitializer)
    {
        $this->_variablePriorInitializer = &$variablePriorInitializer;
    }

    public function createBasicVariable()
    {
        $initializer = $this->_variablePriorInitializer;
        $newVar = new Variable("variable", $initializer());
        return $newVar;
    }

    public function createKeyedVariable($key)
    {
        $initializer = $this->_variablePriorInitializer;
        $newVar = new KeyedVariable($key, "key variable", $initializer());
        return $newVar;
    }
}

?>
