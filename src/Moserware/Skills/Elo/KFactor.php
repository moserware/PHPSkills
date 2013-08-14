<?php

namespace Moserware\Skills\Elo;

class KFactor
{
    const DEFAULT_KFACTOR = 24;
    
    private $_value;

    public function __construct($exactKFactor = self::DEFAULT_KFACTOR)
    {
        $this->_value = $exactKFactor;
    }

    public function getValueForRating($rating)
    {
        return $this->_value;
    }
}

?>