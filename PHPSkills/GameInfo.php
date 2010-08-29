<?php

namespace Moserware\Skills;

require_once(dirname(__FILE__) . "/Rating.php");


/**
 * Parameters about the game for calculating the TrueSkill.
 */
class GameInfo
{
    const DEFAULT_BETA = 4.1666666666666666666666666666667; // Default initial mean / 6
    const DEFAULT_DRAW_PROBABILITY = 0.10;
    const DEFAULT_DYNAMICS_FACTOR = 0.083333333333333333333333333333333; // Default initial mean / 300
    const DEFAULT_INITIAL_MEAN = 25.0;
    const DEFAULT_INITIAL_STANDARD_DEVIATION = 8.3333333333333333333333333333333; // Default initial mean / 3

    private $_initialMean;
    private $_initialStandardDeviation;
    private $_beta;
    private $_dynamicsFactor;
    private $_drawProbability;    
    
    public function __construct($initialMean = self::DEFAULT_INITIAL_MEAN, 
                                $initialStandardDeviation = self::DEFAULT_INITIAL_STANDARD_DEVIATION, 
                                $beta = self::DEFAULT_BETA, 
                                $dynamicsFactor = self::DEFAULT_DYNAMICS_FACTOR, 
                                $drawProbability = self::DEFAULT_DRAW_PROBABILITY)
    {
        $this->_initialMean = $initialMean;
        $this->_initialStandardDeviation = $initialStandardDeviation;
        $this->_beta = $beta;
        $this->_dynamicsFactor = $dynamicsFactor;
        $this->_drawProbability = $drawProbability;
    }   
    

    public function getInitialMean()
    { 
        return $this->_initialMean;
    }
    
    public function getInitialStandardDeviation()
    {
        return $this->_initialStandardDeviation;
    }
    
    public function getBeta()
    {
        return $this->_beta;
    }

    public function getDynamicsFactor()
    {
        return $this->_dynamicsFactor;
    }
    
    public function getDrawProbability()
    {
        return $this->_drawProbability;
    }

    public function getDefaultRating()
    {
        return new Rating($this->_initialMean, $this->_initialStandardDeviation);
    }
}

?>