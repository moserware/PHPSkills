<?php
require_once(dirname(__FILE__) . "/../../PHPSkills/GameInfo.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/Player.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/Rating.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/Team.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/Teams.php");
require_once(dirname(__FILE__) . "/../../PHPSkills/SkillCalculator.php");

use Moserware\Skills\GameInfo;
use Moserware\Skills\Player;
use Moserware\Skills\Rating;
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
        self::twoPlayerTestDrawn($testClass, $calculator);        
        self::twoPlayerChessTestNotDrawn($testClass, $calculator);
        self::oneOnOneMassiveUpsetDrawTest($testClass, $calculator);
    }
    
    public static function testAllTwoTeamScenarios($testClass, SkillCalculator $calculator)
    {
        //OneOnTwoSimpleTest(calculator);            
        //OneOnTwoDrawTest(calculator);
        //OneOnTwoSomewhatBalanced(calculator);
        //OneOnThreeDrawTest(calculator);
        //OneOnThreeSimpleTest(calculator);
        //OneOnSevenSimpleTest(calculator);

        self::twoOnTwoSimpleTest($testClass, $calculator);
        //TwoOnTwoUnbalancedDrawTest(calculator);
        //TwoOnTwoDrawTest(calculator);
        //TwoOnTwoUpsetTest(calculator);            

        //ThreeOnTwoTests(calculator);

        //FourOnFourSimpleTest(calculator);            
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

    private static function twoPlayerTestDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);

        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 25.0, 6.4575196623173081, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 25.0, 6.4575196623173081, $player2NewRating);

        self::assertMatchQuality($testClass, 0.447, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function twoPlayerChessTestNotDrawn($testClass, SkillCalculator $calculator)
    {
        // Inspired by a real bug :-)
        $player1 = new Player(1);
        $player2 = new Player(2);
        $gameInfo = new GameInfo(1200.0, 1200.0 / 3.0, 200.0, 1200.0 / 300.0, 0.03);

        $team1 = new Team($player1, new Rating(1301.0007, 42.9232));
        $team2 = new Team($player2, new Rating(1188.7560, 42.5570));

        $newRatings = $calculator->calculateNewRatings($gameInfo, Teams::concat($team1, $team2), array(1, 2));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 1304.7820836053318, 42.843513887848658, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 1185.0383099003536, 42.485604606897752, $player2NewRating);
    }

    private static function oneOnOneMassiveUpsetDrawTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);

        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());

        $player2 = new Player(2);

        $team2 = new Team($player2, new Rating(50, 12.5));

        $teams = Teams::concat($team1, $team2);

        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1));

        // Winners
        self::assertRating($testClass, 31.662, 7.137, $newRatingsWinLose->getRating($player1));

        // Losers
        self::assertRating($testClass, 35.010, 7.910, $newRatingsWinLose->getRating($player2));

        self::assertMatchQuality($testClass, 0.110, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    //------------------------------------------------------------------------------
    // Two Team Tests
    //------------------------------------------------------------------------------

    private static function twoOnTwoSimpleTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, $gameInfo->getDefaultRating());
        $team1->addPlayer($player2, $gameInfo->getDefaultRating());

        $player3 = new Player(3);
        $player4 = new Player(4);

        $team2 = new Team();
        $team2->addPlayer($player3, $gameInfo->getDefaultRating());
        $team2->addPlayer($player4, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 28.108, 7.774, $newRatingsWinLose->getRating($player1));
        self::assertRating($testClass, 28.108, 7.774, $newRatingsWinLose->getRating($player2));

        // Losers
        self::assertRating($testClass, 21.892, 7.774, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 21.892, 7.774, $newRatingsWinLose->getRating($player4));

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
