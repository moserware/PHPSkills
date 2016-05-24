<?php

namespace Moserware\Skills\Elo;

require_once(dirname(__FILE__) . "/../PairwiseComparison.php");
require_once(dirname(__FILE__) . "/../RankSorter.php");
require_once(dirname(__FILE__) . "/../SkillCalculator.php");

require_once(dirname(__FILE__) . "/../PlayersRange.php");
require_once(dirname(__FILE__) . "/../TeamsRange.php");

use Moserware\Skills\PairwiseComparison;
use Moserware\Skills\RankSorter;
use Moserware\Skills\SkillCalculator;
use Moserware\Skills\SkillCalculatorSupportedOptions;

use Moserware\Skills\PlayersRange;
use Moserware\Skills\TeamsRange;

abstract class TwoPlayerEloCalculator extends SkillCalculator
{
    protected $_kFactor;

    protected function __construct(KFactor $kFactor)        
    {
        parent::__construct(SkillCalculatorSupportedOptions::NONE, TeamsRange::exactly(2), PlayersRange::exactly(1));
        $this->_kFactor = $kFactor;
    }

    public function calculateNewRatings($gameInfo,
                                        array $teamsOfPlayerToRatings,
                                        array $teamRanks)
    {   
        $this->validateTeamCountAndPlayersCountPerTeam($teamsOfPlayerToRatings);
        RankSorter::sort($teamsOfPlayerToRatings, $teamRanks);
        
        $result = array();
        $isDraw = ($teamRanks[0] === $teamRanks[1]);

        $team1 = $teamsOfPlayerToRatings[0];
        $team2 = $teamsOfPlayerToRatings[1];
        
        $player1 = each($team1);
        $player2 = each($team2);
        
        $player1Rating = $player1["value"]->getMean();
        $player2Rating = $player2["value"]->getMean();

        $result[$player1["key"]] = $this->calculateNewRating($gameInfo, $player1Rating, $player2Rating, $isDraw ? PairwiseComparison::DRAW : PairwiseComparison::WIN);
        $result[$player2["key"]] = $this->calculateNewRating($gameInfo, $player2Rating, $player1Rating, $isDraw ? PairwiseComparison::DRAW : PairwiseComparison::LOSE);

        return $result;
    }

    protected function calculateNewRating($gameInfo, $selfRating, $opponentRating, $selfToOpponentComparison)
    {
        $expectedProbability = $this->getPlayerWinProbability($gameInfo, $selfRating, $opponentRating);
        $actualProbability = $this->getScoreFromComparison($selfToOpponentComparison);
        $k = $this->_kFactor->getValueForRating($selfRating);
        $ratingChange = $k * ($actualProbability - $expectedProbability);
        $newRating = $selfRating + $ratingChange;

        return new EloRating($newRating);
    }

    private static function getScoreFromComparison($comparison)
    {
        switch ($comparison)
        {
            case PairwiseComparison::WIN:
                return 1;
            case PairwiseComparison::DRAW:
                return 0.5;
            case PairwiseComparison::LOSE:
                return 0;
            default:
                throw new Exception("Unexpected comparison");
        }
    }

    public abstract function getPlayerWinProbability($gameInfo, $playerRating, $opponentRating);

    public function calculateMatchQuality($gameInfo, array $teamsOfPlayerToRatings)
    {
        validateTeamCountAndPlayersCountPerTeam($teamsOfPlayerToRatings);
        $team1 = $teamsOfPlayerToRatings[0];
        $team2 = $teamsOfPlayerToRatings[1];
        
        $player1 = $team1[0];
        $player2 = $team2[0];
        
        $player1Rating = $player1[1]->getMean();
        $player2Rating = $player2[1]->getMean();
        
        $ratingDifference = $player1Rating - $player2Rating;

        // The TrueSkill paper mentions that they used s1 - s2 (rating difference) to
        // determine match quality. I convert that to a percentage as a delta from 50%
        // using the cumulative density function of the specific curve being used
        $deltaFrom50Percent = abs(getPlayerWinProbability($gameInfo, $player1Rating, $player2Rating) - 0.5);
        return (0.5 - $deltaFrom50Percent) / 0.5;
    }
}

?>