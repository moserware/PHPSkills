<?php
namespace Moserware\Skills\Elo;

use Moserware\Skills\GameInfo;
use Moserware\Numerics\GaussianDistribution;

class GaussianEloCalculator extends TwoPlayerEloCalculator
{
    // From the paper
    const STABLE_KFACTOR = 24;

    public function __construct()        
    {
        parent::__construct(new KFactor(self::STABLE_KFACTOR));
    }
    
    public function getPlayerWinProbability(GameInfo $gameInfo, $playerRating, $opponentRating)
    {
        $ratingDifference = $playerRating - $opponentRating;

        // See equation 1.1 in the TrueSkill paper
        return GaussianDistribution::cumulativeTo(
            $ratingDifference
            /
            (sqrt(2) * $gameInfo->getBeta()));
    }
}

?>