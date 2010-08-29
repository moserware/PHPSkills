<?php
require_once(dirname(__FILE__) . "/../../PHPSkills/GameInfo.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/Player.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/Team.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/Teams.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/SkillCalculator.php");

use Moserware\Skills\GameInfo;
use Moserware\Skills\Player;
use Moserware\Skills\Team;
use Moserware\Skills\Teams;
use Moserware\Skills\SkillCalculator;

class TrueSkillCalculatorTests
{
    const ERROR_TOLERANCE_TRUESKILL = 0.085;
    const ERROR_TOLERANCE_MATCH_QUALITY = 0.0005;

    // These are the roll-up ones

    public static function testAllTwoPlayerScenarios($testClass, SkillCalculator $calculator)
    {
        self::twoPlayerTestNotDrawn($testClass, $calculator);
        //self::twoPlayerTestDrawn($testClass, $calculator);
        //self::oneOnOneMassiveUpsetDrawTest($testClass, $calculator);
        //self::twoPlayerChessTestNotDrawn($testClass, $calculator);
    }

    //------------------- Actual Tests ---------------------------
    // If you see more than 3 digits of precision in the decimal point, then the expected values calculated from
    // F# RalfH's implementation with the same input. It didn't support teams, so team values all came from the
    // online calculator at http://atom.research.microsoft.com/trueskill/rankcalculator.aspx
    //
    // All match quality expected values came from the online calculator

    // In both cases, there may be some discrepancy after the first decimal point. I think this is due to my implementation
    // using slightly higher precision in GaussianDistribution.

    //------------------------------------------------------------------------------
    // Two Player Tests
    //------------------------------------------------------------------------------

    private static function twoPlayerTestNotDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());;
        $teams = Teams::concat($team1, $team2);

        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 29.39583201999924, 7.171475587326186, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 20.60416798000076, 7.171475587326186, $player2NewRating);

        self::assertMatchQuality($testClass, 0.447, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function assertRating($testClass, $expectedMean, $expectedStandardDeviation, $actual)
    {
        $testClass->assertEquals($expectedMean, $actual->getMean(), '',  self::ERROR_TOLERANCE_TRUESKILL);
        $testClass->assertEquals($expectedStandardDeviation, $actual->getStandardDeviation(), '', self::ERROR_TOLERANCE_TRUESKILL);
    }

    private static function assertMatchQuality($testClass, $expectedMatchQuality, $actualMatchQuality)
    {
        $testClass->assertEquals($expectedMatchQuality, $actualMatchQuality, '', self::ERROR_TOLERANCE_MATCH_QUALITY);
    }
}

?>
