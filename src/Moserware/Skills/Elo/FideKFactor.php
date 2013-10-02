<?php
namespace Moserware\Skills\Elo;

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
?>