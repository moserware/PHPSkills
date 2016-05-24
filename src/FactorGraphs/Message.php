<?php namespace Moserware\Skills\FactorGraphs;

class Message
{
    private $_name;
    private $_value;

    public function __construct($value = null, $name = null)
    {
        $this->_name = $name;
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function __toString()
    {
        return (string)$this->_name;
    }
}