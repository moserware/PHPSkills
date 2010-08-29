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
        self::oneOnTwoSimpleTest($testClass, $calculator);        
        self::oneOnTwoSomewhatBalanced($testClass, $calculator);
        self::oneOnTwoDrawTest($testClass, $calculator);
        self::oneOnThreeDrawTest($testClass, $calculator);
        self::oneOnThreeSimpleTest($testClass, $calculator);
        self::oneOnSevenSimpleTest($testClass, $calculator);

        self::twoOnTwoSimpleTest($testClass, $calculator);
        self::twoOnTwoUnbalancedDrawTest($testClass, $calculator);
        self::twoOnTwoDrawTest($testClass, $calculator);
        self::twoOnTwoUpsetTest($testClass, $calculator);

        self::threeOnTwoTests($testClass, $calculator);

        self::fourOnFourSimpleTest($testClass, $calculator);
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

    private static function oneOnTwoSimpleTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, $gameInfo->getDefaultRating());

        $player2 = new Player(2);
        $player3 = new Player(3);

        $team2 = new Team();
        $team2->addPlayer($player2, $gameInfo->getDefaultRating());
        $team2->addPlayer($player3, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 33.730, 7.317, $newRatingsWinLose->getRating($player1));

        // Losers
        self::assertRating($testClass, 16.270, 7.317, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 16.270, 7.317, $newRatingsWinLose->getRating($player3));

        self::assertMatchQuality($testClass, 0.135, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

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

    private static function oneOnTwoSomewhatBalanced($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, new Rating(40, 6));

        $player2 = new Player(2);
        $player3 = new Player(3);

        $team2 = new Team();
        $team2->addPlayer($player2, new Rating(20, 7));
        $team2->addPlayer($player3, new Rating(25, 8));

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 42.744, 5.602, $newRatingsWinLose->getRating($player1));

        // Losers
        self::assertRating($testClass, 16.266, 6.359, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 20.123, 7.028, $newRatingsWinLose->getRating($player3));

        self::assertMatchQuality($testClass, 0.478, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function oneOnThreeSimpleTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, $gameInfo->getDefaultRating());

        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);

        $team2 = new Team();
        $team2->addPlayer($player2, $gameInfo->getDefaultRating());
        $team2->addPlayer($player3, $gameInfo->getDefaultRating());
        $team2->addPlayer($player4, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 36.337, 7.527, $newRatingsWinLose->getRating($player1));

        // Losers
        self::assertRating($testClass, 13.663, 7.527, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 13.663, 7.527, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 13.663, 7.527, $newRatingsWinLose->getRating($player4));

        self::assertMatchQuality($testClass, 0.012, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function oneOnTwoDrawTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, $gameInfo->getDefaultRating());;

        $player2 = new Player(2);
        $player3 = new Player(3);

        $team2 = new Team();
        $team2->addPlayer($player2, $gameInfo->getDefaultRating());
        $team2->addPlayer($player3, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1));

        // Winners
        self::assertRating($testClass, 31.660, 7.138, $newRatingsWinLose->getRating($player1));

        // Losers
        self::assertRating($testClass, 18.340, 7.138, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 18.340, 7.138, $newRatingsWinLose->getRating($player3));

        self::assertMatchQuality($testClass, 0.135, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function oneOnThreeDrawTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, $gameInfo->getDefaultRating());

        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);

        $team2 = new Team();
        $team2->addPlayer($player2, $gameInfo->getDefaultRating());
        $team2->addPlayer($player3, $gameInfo->getDefaultRating());
        $team2->addPlayer($player4, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1));

        // Winners
        self::assertRating($testClass, 34.990, 7.455, $newRatingsWinLose->getRating($player1));

        // Losers
        self::assertRating($testClass, 15.010, 7.455, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 15.010, 7.455, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 15.010, 7.455, $newRatingsWinLose->getRating($player4));

        self::assertMatchQuality($testClass, 0.012, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function oneOnSevenSimpleTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, $gameInfo->getDefaultRating());

        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);
        $player5 = new Player(5);
        $player6 = new Player(6);
        $player7 = new Player(7);
        $player8 = new Player(8);

        $team2 = new Team();
        $team2->addPlayer($player2, $gameInfo->getDefaultRating());
        $team2->addPlayer($player3, $gameInfo->getDefaultRating());
        $team2->addPlayer($player4, $gameInfo->getDefaultRating());
        $team2->addPlayer($player5, $gameInfo->getDefaultRating());
        $team2->addPlayer($player6, $gameInfo->getDefaultRating());
        $team2->addPlayer($player7, $gameInfo->getDefaultRating());
        $team2->addPlayer($player8, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 40.582, 7.917, $newRatingsWinLose->getRating($player1));

        // Losers
        self::assertRating($testClass, 9.418, 7.917, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 9.418, 7.917, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 9.418, 7.917, $newRatingsWinLose->getRating($player4));
        self::assertRating($testClass, 9.418, 7.917, $newRatingsWinLose->getRating($player5));
        self::assertRating($testClass, 9.418, 7.917, $newRatingsWinLose->getRating($player6));
        self::assertRating($testClass, 9.418, 7.917, $newRatingsWinLose->getRating($player7));
        self::assertRating($testClass, 9.418, 7.917, $newRatingsWinLose->getRating($player8));

        self::assertMatchQuality($testClass, 0.000, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function threeOnTwoTests($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);

        $team1 = new Team();
        $team1->addPlayer($player1, new Rating(28, 7));
        $team1->addPlayer($player2, new Rating(27, 6));
        $team1->addPlayer($player3, new Rating(26, 5));


        $player4 = new Player(4);
        $player5 = new Player(5);

        $team2 = new Team();
        $team2->addPlayer($player4, new Rating(30, 4));
        $team2->addPlayer($player5, new Rating(31, 3));

        $gameInfo = new GameInfo();

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLoseExpected = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 28.658, 6.770, $newRatingsWinLoseExpected->getRating($player1));
        self::assertRating($testClass, 27.484, 5.856, $newRatingsWinLoseExpected->getRating($player2));
        self::assertRating($testClass, 26.336, 4.917, $newRatingsWinLoseExpected->getRating($player3));

        // Losers
        self::assertRating($testClass, 29.785, 3.958, $newRatingsWinLoseExpected->getRating($player4));
        self::assertRating($testClass, 30.879, 2.983, $newRatingsWinLoseExpected->getRating($player5));

        $newRatingsWinLoseUpset = $calculator->calculateNewRatings($gameInfo, Teams::concat($team1, $team2), array(2, 1));

        // Winners
        self::assertRating($testClass, 32.012, 3.877, $newRatingsWinLoseUpset->getRating($player4));
        self::assertRating($testClass, 32.132, 2.949, $newRatingsWinLoseUpset->getRating($player5));

        // Losers
        self::assertRating($testClass, 21.840, 6.314, $newRatingsWinLoseUpset->getRating($player1));
        self::assertRating($testClass, 22.474, 5.575, $newRatingsWinLoseUpset->getRating($player2));
        self::assertRating($testClass, 22.857, 4.757, $newRatingsWinLoseUpset->getRating($player3));

        self::assertMatchQuality($testClass, 0.254, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function twoOnTwoUnbalancedDrawTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, new Rating(15, 8));
        $team1->addPlayer($player2, new Rating(20, 6));

        $player3 = new Player(3);
        $player4 = new Player(4);

        $team2 = new Team();
        $team2->addPlayer($player3, new Rating(25, 4));
        $team2->addPlayer($player4, new Rating(30, 3));

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1));

        // Winners
        self::assertRating($testClass, 21.570, 6.556, $newRatingsWinLose->getRating($player1));
        self::assertRating($testClass, 23.696, 5.418, $newRatingsWinLose->getRating($player2));

        // Losers
        self::assertRating($testClass, 23.357, 3.833, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 29.075, 2.931, $newRatingsWinLose->getRating($player4));

        self::assertMatchQuality($testClass, 0.214, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function twoOnTwoUpsetTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, new Rating(20, 8));
        $team1->addPlayer($player2, new Rating(25, 6));

        $player3 = new Player(3);
        $player4 = new Player(4);

        $team2 = new Team();
        $team2->addPlayer($player3, new Rating(35, 7));
        $team2->addPlayer($player4, new Rating(40, 5));

        $teams = Teams::concat($team1, $team2);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 29.698, 7.008, $newRatingsWinLose->getRating($player1));
        self::assertRating($testClass, 30.455, 5.594, $newRatingsWinLose->getRating($player2));

        // Losers
        self::assertRating($testClass, 27.575, 6.346, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 36.211, 4.768, $newRatingsWinLose->getRating($player4));

        self::assertMatchQuality($testClass, 0.084, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function fourOnFourSimpleTest($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, $gameInfo->getDefaultRating());
        $team1->addPlayer($player2, $gameInfo->getDefaultRating());
        $team1->addPlayer($player3, $gameInfo->getDefaultRating());
        $team1->addPlayer($player4, $gameInfo->getDefaultRating());;

        $player5 = new Player(5);
        $player6 = new Player(6);
        $player7 = new Player(7);
        $player8 = new Player(8);

        $team2 = new Team();
        $team2->addPlayer($player5, $gameInfo->getDefaultRating());
        $team2->addPlayer($player6, $gameInfo->getDefaultRating());
        $team2->addPlayer($player7, $gameInfo->getDefaultRating());
        $team2->addPlayer($player8, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);

        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        // Winners
        self::assertRating($testClass, 27.198, 8.059, $newRatingsWinLose->getRating($player1));
        self::assertRating($testClass, 27.198, 8.059, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 27.198, 8.059, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 27.198, 8.059, $newRatingsWinLose->getRating($player4));

        // Losers
        self::assertRating($testClass, 22.802, 8.059, $newRatingsWinLose->getRating($player5));
        self::assertRating($testClass, 22.802, 8.059, $newRatingsWinLose->getRating($player6));
        self::assertRating($testClass, 22.802, 8.059, $newRatingsWinLose->getRating($player7));
        self::assertRating($testClass, 22.802, 8.059, $newRatingsWinLose->getRating($player8));

        self::assertMatchQuality($testClass, 0.447, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function twoOnTwoDrawTest($testClass, SkillCalculator $calculator)
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
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1));

        // Winners
        self::assertRating($testClass, 25, 7.455, $newRatingsWinLose->getRating($player1));
        self::assertRating($testClass, 25, 7.455, $newRatingsWinLose->getRating($player2));

        // Losers
        self::assertRating($testClass, 25, 7.455, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 25, 7.455, $newRatingsWinLose->getRating($player4));

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
