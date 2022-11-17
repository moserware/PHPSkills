<?php namespace Moserware\Skills\Tests\Elo;

use Moserware\Skills\Elo\EloRating;
use Moserware\Skills\Elo\FideEloCalculator;
use Moserware\Skills\GameInfo;
use Moserware\Skills\PairwiseComparison;
use Moserware\Skills\Tests\TestCase;

class EloAssert
{
    const ERROR_TOLERANCE = 0.1;

    public static function assertChessRating(
        TestCase $testClass,
        FideEloCalculator $twoPlayerEloCalculator,
        $player1BeforeRating,
        $player2BeforeRating,
        $player1Result,
        $player1AfterRating,
        $player2AfterRating)
    {
        $player1 = "Player1";
        $player2 = "Player2";

        $teams = array(
            array($player1 => new EloRating($player1BeforeRating)),
            array($player2 => new EloRating($player2BeforeRating))
        );

        $chessGameInfo = new GameInfo(1200, 0, 200);

        $ranks = PairwiseComparison::getRankFromComparison($player1Result);

        $result = $twoPlayerEloCalculator->calculateNewRatings(
            $chessGameInfo,
            $teams,
            $ranks
        );

        $testClass->assertEqualsWithDelta($player1AfterRating, $result[$player1]->getMean(), self::ERROR_TOLERANCE);
        $testClass->assertEqualsWithDelta($player2AfterRating, $result[$player2]->getMean(), self::ERROR_TOLERANCE);
    }
}