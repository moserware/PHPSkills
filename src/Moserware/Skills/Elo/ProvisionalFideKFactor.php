<?php
namespace Moserware\Skills\Elo;

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