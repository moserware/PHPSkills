<?php

namespace Moserware\Skills\TrueSkill;

require_once(dirname(__FILE__) . "/../GameInfo.php");
require_once(dirname(__FILE__) . "/../Guard.php");
require_once(dirname(__FILE__) . "/../PairwiseComparison.php");
require_once(dirname(__FILE__) . "/../RankSorter.php");
require_once(dirname(__FILE__) . "/../Rating.php");
require_once(dirname(__FILE__) . "/../RatingContainer.php");
require_once(dirname(__FILE__) . "/../SkillCalculator.php");

require_once(dirname(__FILE__) . "/../PlayersRange.php");
require_once(dirname(__FILE__) . "/../TeamsRange.php");

require_once(dirname(__FILE__) . "/../Numerics/BasicMath.php");

require_once(dirname(__FILE__) . "/DrawMargin.php");
require_once(dirname(__FILE__) . "/TruncatedGaussianCorrectionFunctions.php");

use Moserware\Skills\GameInfo;
use Moserware\Skills\Guard;
use Moserware\Skills\PairwiseComparison;
use Moserware\Skills\RankSorter;
use Moserware\Skills\Rating;
use Moserware\Skills\RatingContainer;
use Moserware\Skills\SkillCalculator;
use Moserware\Skills\SkillCalculatorSupportedOptions;

use Moserware\Skills\PlayersRange;
use Moserware\Skills\TeamsRange;

/**
 * Calculates the new ratings for only two players.
 * 
 * When you only have two players, a lot of the math simplifies. The main purpose of this class
 * is to show the bare minimum of what a TrueSkill implementation should have.
 */

class TwoPlayerTrueSkillCalculator extends SkillCalculator
{
    public function __construct()
    {
        parent::__construct(SkillCalculatorSupportedOptions::NONE, TeamsRange::exactly(2), PlayersRange::exactly(1));
    }

    public function calculateNewRatings(GameInfo $gameInfo,
                                        array $teams,
                                        array $teamRanks)
    {
        // Basic argument checking
        Guard::argumentNotNull($gameInfo, "gameInfo");
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        // Make sure things are in order
        RankSorter::sort($teams, $teamRanks);
        
        // Since we verified that each team has one player, we know the player is the first one
        $winningTeamPlayers = $teams[0]->getAllPlayers();
        $winner = $winningTeamPlayers[0];
        $winnerPreviousRating = $teams[0]->getRating($winner);
        
        $losingTeamPlayers = $teams[1]->getAllPlayers();
        $loser = $losingTeamPlayers[0];
        $loserPreviousRating = $teams[1]->getRating($loser);

        $wasDraw = ($teamRanks[0] == $teamRanks[1]);

        $results = new RatingContainer();

        $results->setRating($winner, self::calculateNewRating($gameInfo,
                                                              $winnerPreviousRating,
                                                              $loserPreviousRating,
                                                              $wasDraw ? PairwiseComparison::DRAW
                                                                       : PairwiseComparison::WIN));

        $results->setRating($loser, self::calculateNewRating($gameInfo,
                                                             $loserPreviousRating,
                                                             $winnerPreviousRating,
                                                             $wasDraw ? PairwiseComparison::DRAW
                                                                      : PairwiseComparison::LOSE));

        // And we're done!
        return $results;
    }

    private static function calculateNewRating(GameInfo $gameInfo, Rating $selfRating, Rating $opponentRating, $comparison)
    {
        $drawMargin = DrawMargin::getDrawMarginFromDrawProbability($gameInfo->getDrawProbability(),
                                                                   $gameInfo->getBeta());

        $c =
            sqrt(
                square($selfRating->getStandardDeviation())
                +
                square($opponentRating->getStandardDeviation())
                +
                2*square($gameInfo->getBeta()));

        $winningMean = $selfRating->getMean();
        $losingMean = $opponentRating->getMean();

        switch ($comparison)
        {
            case PairwiseComparison::WIN:
            case PairwiseComparison::DRAW:
                // NOP
                break;
            case PairwiseComparison::LOSE:
                $winningMean = $opponentRating->getMean();
                $losingMean = $selfRating->getMean();
                break;
        }

        $meanDelta = $winningMean - $losingMean;

        if ($comparison != PairwiseComparison::DRAW)
        {
            // non-draw case
            $v = TruncatedGaussianCorrectionFunctions::vExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = (int) $comparison;
        }
        else
        {
            $v = TruncatedGaussianCorrectionFunctions::vWithinMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wWithinMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = 1;
        }

        $meanMultiplier = (square($selfRating->getStandardDeviation()) + square($gameInfo->getDynamicsFactor()))/$c;

        $varianceWithDynamics = square($selfRating->getStandardDeviation()) + square($gameInfo->getDynamicsFactor());
        $stdDevMultiplier = $varianceWithDynamics/square($c);

        $newMean = $selfRating->getMean() + ($rankMultiplier*$meanMultiplier*$v);
        $newStdDev = sqrt($varianceWithDynamics*(1 - $w*$stdDevMultiplier));

        return new Rating($newMean, $newStdDev);
    }

    /**
     * {@inheritdoc }
     */
    public function calculateMatchQuality(GameInfo $gameInfo, array $teams)
    {
        Guard::argumentNotNull($gameInfo, "gameInfo");
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        $team1 = $teams[0];
        $team2 = $teams[1];

        $team1Ratings = $team1->getAllRatings();
        $team2Ratings = $team2->getAllRatings();

        $player1Rating = $team1Ratings[0];
        $player2Rating = $team2Ratings[0];

        // We just use equation 4.1 found on page 8 of the TrueSkill 2006 paper:
        $betaSquared = square($gameInfo->getBeta());
        $player1SigmaSquared = square($player1Rating->getStandardDeviation());
        $player2SigmaSquared = square($player2Rating->getStandardDeviation());

        // This is the square root part of the equation:
        $sqrtPart =
            sqrt(
                (2*$betaSquared)
                /
                (2*$betaSquared + $player1SigmaSquared + $player2SigmaSquared));

        // This is the exponent part of the equation:
        $expPart =
            exp(
                (-1*square($player1Rating->getMean() - $player2Rating->getMean()))
                /
                (2*(2*$betaSquared + $player1SigmaSquared + $player2SigmaSquared)));

        return $sqrtPart*$expPart;
    }
}
?>
