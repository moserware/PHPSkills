<?php
namespace Moserware\Skills\Elo;

require_once(dirname(__FILE__) . "/KFactor.php");

// see http://ratings.fide.com/calculator_rtd.phtml for details
class FideKFactor extends KFactor
{
    public function getValueForRating($rating)
    {
        if ($rating < 2400)
        {
            return 15;
        }

        return 10;
    }    
}

/**
 * Indicates someone who has played less than 30 games.
 */
class ProvisionalFideKFactor extends FideKFactor
{
    public function getValueForRating($rating)
    {
        return 25;
    }
}
?>