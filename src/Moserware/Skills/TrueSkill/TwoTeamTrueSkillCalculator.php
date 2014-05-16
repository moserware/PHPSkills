<?php

namespace Moserware\Skills\TrueSkill;

use Moserware\Skills\GameInfo;
use Moserware\Skills\Guard;
use Moserware\Skills\Numerics\BasicMath;
use Moserware\Skills\PairwiseComparison;
use Moserware\Skills\RankSorter;
use Moserware\Skills\Rating;
use Moserware\Skills\RatingContainer;
use Moserware\Skills\SkillCalculator;
use Moserware\Skills\SkillCalculatorSupportedOptions;

use Moserware\Skills\PlayersRange;
use Moserware\Skills\TeamsRange;

use Moserware\Skills\Team;

/**
 * Calculates new ratings for only two teams where each team has 1 or more players.
 * 
 * When you only have two teams, the math is still simple: no factor graphs are used yet.
 */
class TwoTeamTrueSkillCalculator extends SkillCalculator
{
    public function __construct()
    {
        parent::__construct(SkillCalculatorSupportedOptions::NONE, TeamsRange::exactly(2), PlayersRange::atLeast(1));
    }

    public function calculateNewRatings(GameInfo $gameInfo,
                                        array $teams,
                                        array $teamRanks)
    {
        Guard::argumentNotNull($gameInfo, "gameInfo");
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        RankSorter::sort($teams, $teamRanks);

        $team1 = $teams[0];
        $team2 = $teams[1];

        $wasDraw = ($teamRanks[0] == $teamRanks[1]);

        $results = new RatingContainer();

        self::updatePlayerRatings($gameInfo,
                                  $results,
                                  $team1,
                                  $team2,
                                  $wasDraw ? PairwiseComparison::DRAW : PairwiseComparison::WIN);

        self::updatePlayerRatings($gameInfo,
                                  $results,
                                  $team2,
                                  $team1,
                                  $wasDraw ? PairwiseComparison::DRAW : PairwiseComparison::LOSE);

        return $results;
    }

    private static function updatePlayerRatings(GameInfo $gameInfo,
                                                RatingContainer $newPlayerRatings,
                                                Team $selfTeam,
                                                Team $otherTeam,
                                                $selfToOtherTeamComparison)
    {
        $drawMargin = DrawMargin::getDrawMarginFromDrawProbability($gameInfo->getDrawProbability(),
                                                                   $gameInfo->getBeta());

        $betaSquared = BasicMath::square($gameInfo->getBeta());
        $tauSquared = BasicMath::square($gameInfo->getDynamicsFactor());

        $totalPlayers = $selfTeam->count() + $otherTeam->count();

        $meanGetter =
            function($currentRating)
            {
                return $currentRating->getMean();
            };

        $selfMeanSum = BasicMath::sum($selfTeam->getAllRatings(), $meanGetter);
        $otherTeamMeanSum = BasicMath::sum($otherTeam->getAllRatings(), $meanGetter);

        $varianceGetter =
            function($currentRating)
            {
                return BasicMath::square($currentRating->getStandardDeviation());
            };

        $c = sqrt(
            BasicMath::sum($selfTeam->getAllRatings(), $varianceGetter)
                  +
            BasicMath::sum($otherTeam->getAllRatings(), $varianceGetter)
                  +
                  $totalPlayers*$betaSquared);

        $winningMean = $selfMeanSum;
        $losingMean = $otherTeamMeanSum;

        switch ($selfToOtherTeamComparison)
        {
            case PairwiseComparison::WIN:
            case PairwiseComparison::DRAW:
                // NOP
                break;
            case PairwiseComparison::LOSE:
                $winningMean = $otherTeamMeanSum;
                $losingMean = $selfMeanSum;
                break;
        }

        $meanDelta = $winningMean - $losingMean;

        if ($selfToOtherTeamComparison != PairwiseComparison::DRAW)
        {
            // non-draw case
            $v = TruncatedGaussianCorrectionFunctions::vExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = (int) $selfToOtherTeamComparison;
        }
        else
        {
            // assume draw
            $v = TruncatedGaussianCorrectionFunctions::vWithinMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wWithinMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = 1;
        }

        $selfTeamAllPlayers = $selfTeam->getAllPlayers();
        foreach ($selfTeamAllPlayers as $selfTeamCurrentPlayer)
        {
            $localSelfTeamCurrentPlayer = $selfTeamCurrentPlayer;
            $previousPlayerRating = $selfTeam->getRating($localSelfTeamCurrentPlayer);

            $meanMultiplier = (BasicMath::square($previousPlayerRating->getStandardDeviation()) + $tauSquared)/$c;
            $stdDevMultiplier = (BasicMath::square($previousPlayerRating->getStandardDeviation()) + $tauSquared)/BasicMath::square($c);

            $playerMeanDelta = ($rankMultiplier*$meanMultiplier*$v);
            $newMean = $previousPlayerRating->getMean() + $playerMeanDelta;

            $newStdDev =
                sqrt((BasicMath::square($previousPlayerRating->getStandardDeviation()) + $tauSquared)*(1 - $w*$stdDevMultiplier));

            $newPlayerRatings->setRating($localSelfTeamCurrentPlayer, new Rating($newMean, $newStdDev));
        }
    }

    /**
     * {@inheritdoc }
     */
    public function calculateMatchQuality(GameInfo $gameInfo,
                                          array &$teams)
    {
        Guard::argumentNotNull($gameInfo, "gameInfo");
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        // We've verified that there's just two teams
        $team1Ratings = $teams[0]->getAllRatings();
        $team1Count = count($team1Ratings);

        $team2Ratings = $teams[1]->getAllRatings();
        $team2Count = count($team2Ratings);

        $totalPlayers = $team1Count + $team2Count;

        $betaSquared = BasicMath::square($gameInfo->getBeta());

        $meanGetter =
            function($currentRating)
            {
                return $currentRating->getMean();
            };

        $varianceGetter =
            function($currentRating)
            {
                return BasicMath::square($currentRating->getStandardDeviation());
            };

        $team1MeanSum = BasicMath::sum($team1Ratings, $meanGetter);
        $team1StdDevSquared = BasicMath::sum($team1Ratings, $varianceGetter);

        $team2MeanSum = BasicMath::sum($team2Ratings, $meanGetter);
        $team2SigmaSquared = BasicMath::sum($team2Ratings, $varianceGetter);

        // This comes from equation 4.1 in the TrueSkill paper on page 8
        // The equation was broken up into the part under the square root sign and
        // the exponential part to make the code easier to read.

        $sqrtPart
            = sqrt(
                ($totalPlayers*$betaSquared)
                /
                ($totalPlayers*$betaSquared + $team1StdDevSquared + $team2SigmaSquared)
                );

        $expPart
            = exp(
                (-1*BasicMath::square($team1MeanSum - $team2MeanSum))
                /
                (2*($totalPlayers*$betaSquared + $team1StdDevSquared + $team2SigmaSquared))
                );

        return $expPart*$sqrtPart;
    }
}
?>
