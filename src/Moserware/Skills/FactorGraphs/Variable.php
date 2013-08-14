<?php
namespace Moserware\Skills\FactorGraphs;

class Variable
{
    private $_name;
    private $_prior;
    private $_value;

    public function __construct($name, $prior)
    {
        $this->_name = "Variable[" . $name . "]";
        $this->_prior = $prior;
        $this->resetToPrior();
    }

    public function getValue()
    {
        $value = $this->_value;
        return $value;
    }

    public function setValue($value)
    {        
        $this->_value = $value;
    }

    public function resetToPrior()
    {
        $this->_value = $this->_prior;
    }

    public function __toString()
    {
        return $this->_name;
    }
}

class DefaultVariable extends Variable
{
    public function __construct()
    {
        parent::__construct("Default", null);
    }

    public function getValue()
    {
        return null;
    }

    public function setValue($value)
    {
        throw new Exception();
    }
}

class KeyedVariable extends Variable
{
    private $_key;
    public function __construct($key, $name, $prior)
    {
        parent::__construct($name, $prior);
        $this->_key = $key;
    }

    public function getKey()
    {
        $key = $this->_key;
        return $key;
    }
}

?>
