<?php
namespace Moserware\Skills\Elo;

require_once(dirname(__FILE__) . '/../../Skills/Elo/EloRating.php');
require_once(dirname(__FILE__) . '/../../Skills/GameInfo.php');
require_once(dirname(__FILE__) . '/../../Skills/PairwiseComparison.php');

use Moserware\Skills\GameInfo;
use Moserware\Skills\PairwiseComparison;

class EloAssert
{
    const ERROR_TOLERANCE = 0.1;
    
    public static function assertChessRating(
                                            $testClass,
                                            $twoPlayerEloCalculator,
                                            $player1BeforeRating,
                                            $player2BeforeRating,
                                            $player1Result,
                                            $player1AfterRating,
                                            $player2AfterRating)
    {
            $player1 = "Player1";
            $player2 = "Player2";
            
            $teams = array(
                           array( $player1 => new EloRating($player1BeforeRating) ),
                           array( $player2 => new EloRating($player2BeforeRating) )
                           );        
                           
            $chessGameInfo = new GameInfo(1200, 0, 200);
                      
            $ranks = PairwiseComparison::getRankFromComparison($player1Result);

            $result = $twoPlayerEloCalculator->calculateNewRatings(
                $chessGameInfo,
                $teams,
                $ranks);
                
            $testClass->assertEquals($player1AfterRating, $result[$player1]->getMean(), '', self::ERROR_TOLERANCE);
            $testClass->assertEquals($player2AfterRating, $result[$player2]->getMean(), '', self::ERROR_TOLERANCE);
    }    
}
?>

