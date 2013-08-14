<?php
namespace Moserware\Skills;

/**
 * Represents a comparison between two players.
 * @internal The actual values for the enum were chosen so that the also correspond to the multiplier for updates to means.
 */
class PairwiseComparison
{
    const WIN = 1;
    const DRAW = 0;
    const LOSE = -1;

    public static function getRankFromComparison($comparison)
    {
        switch ($comparison) {
            case PairwiseComparison::WIN:
                return array(1,2);
            case PairwiseComparison::LOSE:
                return array(2,1);
            default:
                return array(1,1);
        }
    }
}

?>