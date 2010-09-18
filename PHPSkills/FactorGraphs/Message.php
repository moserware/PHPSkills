<?php
namespace Moserware\Skills\FactorGraphs;

class Message
{
    private $_nameFormat;
    private $_nameFormatArgs;
    private $_value;

    public function __construct($value = null, $nameFormat = null, $args = null)
    {
        $this->_nameFormat = $nameFormat;
        $this->_nameFormatArgs = $args;
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function __toString()
    {
        return $this->_nameFormat; //return (_NameFormat == null) ? base.ToString() : String.Format(_NameFormat, _NameFormatArgs);
    }
}

?>
