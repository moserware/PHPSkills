<?php
require_once(dirname(__FILE__) . "/../../Skills/GameInfo.php");
require_once(dirname(__FILE__) . "/../../Skills/Player.php");
require_once(dirname(__FILE__) . "/../../Skills/Rating.php");
require_once(dirname(__FILE__) . "/../../Skills/Team.php");
require_once(dirname(__FILE__) . "/../../Skills/Teams.php");
require_once(dirname(__FILE__) . "/../../Skills/SkillCalculator.php");

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

    public static function testAllMultipleTeamScenarios($testClass, SkillCalculator $calculator)
    {
        self::threeTeamsOfOneNotDrawn($testClass, $calculator);
        self::threeTeamsOfOneDrawn($testClass, $calculator);
        self::fourTeamsOfOneNotDrawn($testClass, $calculator);
        self::fiveTeamsOfOneNotDrawn($testClass, $calculator);
        self::eightTeamsOfOneDrawn($testClass, $calculator);
        self::eightTeamsOfOneUpset($testClass, $calculator);
        self::sixteenTeamsOfOneNotDrawn($testClass, $calculator);
        self::twoOnFourOnTwoWinDraw($testClass, $calculator);
    }

    public static function testPartialPlayScenarios($testClass, SkillCalculator $calculator)
    {
        self::oneOnTwoBalancedPartialPlay($testClass, $calculator);
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


    private static function threeTeamsOfOneNotDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);

        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());
        $team3 = new Team($player3, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2, $team3);
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2, 3));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 31.675352419172107, 6.6559853776206905, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 25.000000000003912, 6.2078966412243233, $player2NewRating);

        $player3NewRating = $newRatings->getRating($player3);
        self::assertRating($testClass, 18.324647580823971, 6.6559853776218318, $player3NewRating);

        self::assertMatchQuality($testClass, 0.200, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function threeTeamsOfOneDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);

        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());
        $team3 = new Team($player3, $gameInfo->getDefaultRating());
        
        $teams = Teams::concat($team1, $team2, $team3);
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1, 1));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 25.000, 5.698, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 25.000, 5.695, $player2NewRating);

        $player3NewRating = $newRatings->getRating($player3);
        self::assertRating($testClass, 25.000, 5.698, $player3NewRating);

        self::assertMatchQuality($testClass, 0.200, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function fourTeamsOfOneNotDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);
        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());
        $team3 = new Team($player3, $gameInfo->getDefaultRating());
        $team4 = new Team($player4, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2, $team3, $team4);

        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2, 3, 4));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 33.206680965631264, 6.3481091698077057, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 27.401454693843323, 5.7871629348447584, $player2NewRating);

        $player3NewRating = $newRatings->getRating($player3);
        self::assertRating($testClass, 22.598545306188374, 5.7871629348413451, $player3NewRating);

        $player4NewRating = $newRatings->getRating($player4);
        self::assertRating($testClass, 16.793319034361271, 6.3481091698144967, $player4NewRating);

        self::assertMatchQuality($testClass, 0.089, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function fiveTeamsOfOneNotDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);
        $player5 = new Player(5);
        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());
        $team3 = new Team($player3, $gameInfo->getDefaultRating());
        $team4 = new Team($player4, $gameInfo->getDefaultRating());
        $team5 = new Team($player5, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2, $team3, $team4, $team5);
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2, 3, 4, 5));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 34.363135705841188, 6.1361528798112692, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 29.058448805636779, 5.5358352402833413, $player2NewRating);

        $player3NewRating = $newRatings->getRating($player3);
        self::assertRating($testClass, 25.000000000031758, 5.4200805474429847, $player3NewRating);

        $player4NewRating = $newRatings->getRating($player4);
        self::assertRating($testClass, 20.941551194426314, 5.5358352402709672, $player4NewRating);

        $player5NewRating = $newRatings->getRating($player5);
        self::assertRating($testClass, 15.636864294158848, 6.136152879829349, $player5NewRating);

        self::assertMatchQuality($testClass, 0.040, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function eightTeamsOfOneDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);
        $player5 = new Player(5);
        $player6 = new Player(6);
        $player7 = new Player(7);
        $player8 = new Player(8);
        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());
        $team3 = new Team($player3, $gameInfo->getDefaultRating());
        $team4 = new Team($player4, $gameInfo->getDefaultRating());
        $team5 = new Team($player5, $gameInfo->getDefaultRating());
        $team6 = new Team($player6, $gameInfo->getDefaultRating());
        $team7 = new Team($player7, $gameInfo->getDefaultRating());
        $team8 = new Team($player8, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2, $team3, $team4, $team5, $team6, $team7, $team8);
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 1, 1, 1, 1, 1, 1, 1));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 25.000, 4.592, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 25.000, 4.583, $player2NewRating);

        $player3NewRating = $newRatings->getRating($player3);
        self::assertRating($testClass, 25.000, 4.576, $player3NewRating);

        $player4NewRating = $newRatings->getRating($player4);
        self::assertRating($testClass, 25.000, 4.573, $player4NewRating);

        $player5NewRating = $newRatings->getRating($player5);
        self::assertRating($testClass, 25.000, 4.573, $player5NewRating);

        $player6NewRating = $newRatings->getRating($player6);
        self::assertRating($testClass, 25.000, 4.576, $player6NewRating);

        $player7NewRating = $newRatings->getRating($player7);
        self::assertRating($testClass, 25.000, 4.583, $player7NewRating);

        $player8NewRating = $newRatings->getRating($player8);
        self::assertRating($testClass, 25.000, 4.592, $player8NewRating);

        self::AssertMatchQuality($testClass, 0.004, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function eightTeamsOfOneUpset($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);
        $player5 = new Player(5);
        $player6 = new Player(6);
        $player7 = new Player(7);
        $player8 = new Player(8);

        $gameInfo = new GameInfo();

        $team1 = new Team($player1, new Rating(10, 8));
        $team2 = new Team($player2, new Rating(15, 7));
        $team3 = new Team($player3, new Rating(20, 6));
        $team4 = new Team($player4, new Rating(25, 5));
        $team5 = new Team($player5, new Rating(30, 4));
        $team6 = new Team($player6, new Rating(35, 3));
        $team7 = new Team($player7, new Rating(40, 2));
        $team8 = new Team($player8, new Rating(45, 1));

        $teams = Teams::concat($team1, $team2, $team3, $team4, $team5, $team6, $team7, $team8);
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2, 3, 4, 5, 6, 7, 8));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 35.135, 4.506, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 32.585, 4.037, $player2NewRating);

        $player3NewRating = $newRatings->getRating($player3);
        self::assertRating($testClass, 31.329, 3.756, $player3NewRating);

        $player4NewRating = $newRatings->getRating($player4);
        self::assertRating($testClass, 30.984, 3.453, $player4NewRating);

        $player5NewRating = $newRatings->getRating($player5);
        self::assertRating($testClass, 31.751, 3.064, $player5NewRating);

        $player6NewRating = $newRatings->getRating($player6);
        self::assertRating($testClass, 34.051, 2.541, $player6NewRating);

        $player7NewRating = $newRatings->getRating($player7);
        self::assertRating($testClass, 38.263, 1.849, $player7NewRating);

        $player8NewRating = $newRatings->getRating($player8);
        self::assertRating($testClass, 44.118, 0.983, $player8NewRating);

        self::assertMatchQuality($testClass, 0.000, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    private static function sixteenTeamsOfOneNotDrawn($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);
        $player4 = new Player(4);
        $player5 = new Player(5);
        $player6 = new Player(6);
        $player7 = new Player(7);
        $player8 = new Player(8);
        $player9 = new Player(9);
        $player10 = new Player(10);
        $player11 = new Player(11);
        $player12 = new Player(12);
        $player13 = new Player(13);
        $player14 = new Player(14);
        $player15 = new Player(15);
        $player16 = new Player(16);

        $gameInfo = new GameInfo();

        $team1 = new Team($player1, $gameInfo->getDefaultRating());
        $team2 = new Team($player2, $gameInfo->getDefaultRating());
        $team3 = new Team($player3, $gameInfo->getDefaultRating());
        $team4 = new Team($player4, $gameInfo->getDefaultRating());
        $team5 = new Team($player5, $gameInfo->getDefaultRating());
        $team6 = new Team($player6, $gameInfo->getDefaultRating());
        $team7 = new Team($player7, $gameInfo->getDefaultRating());
        $team8 = new Team($player8, $gameInfo->getDefaultRating());
        $team9 = new Team($player9, $gameInfo->getDefaultRating());
        $team10 = new Team($player10, $gameInfo->getDefaultRating());
        $team11 = new Team($player11, $gameInfo->getDefaultRating());
        $team12 = new Team($player12, $gameInfo->getDefaultRating());
        $team13 = new Team($player13, $gameInfo->getDefaultRating());
        $team14 = new Team($player14, $gameInfo->getDefaultRating());
        $team15 = new Team($player15, $gameInfo->getDefaultRating());
        $team16 = new Team($player16, $gameInfo->getDefaultRating());

        $teams = Teams::concat(
                    $team1, $team2, $team3, $team4, $team5,
                    $team6, $team7, $team8, $team9, $team10,
                    $team11, $team12, $team13, $team14, $team15,
                    $team16);

        $newRatings = $calculator->calculateNewRatings(
                        $gameInfo, $teams,
                        array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16));

        $player1NewRating = $newRatings->getRating($player1);
        self::assertRating($testClass, 40.53945776946920, 5.27581643889050, $player1NewRating);

        $player2NewRating = $newRatings->getRating($player2);
        self::assertRating($testClass, 36.80951229454210, 4.71121217610266, $player2NewRating);

        $player3NewRating = $newRatings->getRating($player3);
        self::assertRating($testClass, 34.34726355544460, 4.52440328139991, $player3NewRating);

        $player4NewRating = $newRatings->getRating($player4);
        self::assertRating($testClass, 32.33614722608720, 4.43258628279632, $player4NewRating);

        $player5NewRating = $newRatings->getRating($player5);
        self::assertRating($testClass, 30.55048814671730, 4.38010805034365, $player5NewRating);

        $player6NewRating = $newRatings->getRating($player6);
        self::assertRating($testClass, 28.89277312234790, 4.34859291776483, $player6NewRating);

        $player7NewRating = $newRatings->getRating($player7);
        self::assertRating($testClass, 27.30952161972210, 4.33037679041216, $player7NewRating);

        $player8NewRating = $newRatings->getRating($player8);
        self::assertRating($testClass, 25.76571046519540, 4.32197078088701, $player8NewRating);

        $player9NewRating = $newRatings->getRating($player9);
        self::assertRating($testClass, 24.23428953480470, 4.32197078088703, $player9NewRating);

        $player10NewRating = $newRatings->getRating($player10);
        self::assertRating($testClass, 22.69047838027800, 4.33037679041219, $player10NewRating);

        $player11NewRating = $newRatings->getRating($player11);
        self::assertRating($testClass, 21.10722687765220, 4.34859291776488, $player11NewRating);

        $player12NewRating = $newRatings->getRating($player12);
        self::assertRating($testClass, 19.44951185328290, 4.38010805034375, $player12NewRating);

        $player13NewRating = $newRatings->getRating($player13);
        self::assertRating($testClass, 17.66385277391300, 4.43258628279643, $player13NewRating);

        $player14NewRating = $newRatings->getRating($player14);
        self::assertRating($testClass, 15.65273644455550, 4.52440328139996, $player14NewRating);

        $player15NewRating = $newRatings->getRating($player15);
        self::assertRating($testClass, 13.19048770545810, 4.71121217610273, $player15NewRating);

        $player16NewRating = $newRatings->getRating($player16);
        self::assertRating($testClass, 9.46054223053080, 5.27581643889032, $player16NewRating);
    }

    private static function twoOnFourOnTwoWinDraw($testClass, SkillCalculator $calculator)
    {
        $player1 = new Player(1);
        $player2 = new Player(2);

        $gameInfo = new GameInfo();

        $team1 = new Team();
        $team1->addPlayer($player1, new Rating(40,4));
        $team1->addPlayer($player2, new Rating(45,3));

        $player3 = new Player(3);
        $player4 = new Player(4);
        $player5 = new Player(5);
        $player6 = new Player(6);

        $team2 = new Team();
        $team2->addPlayer($player3, new Rating(20, 7));
        $team2->addPlayer($player4, new Rating(19, 6));
        $team2->addPlayer($player5, new Rating(30, 9));
        $team2->addPlayer($player6, new Rating(10, 4));

        $player7 = new Player(7);
        $player8 = new Player(8);

        $team3 = new Team();
        $team3->addPlayer($player7, new Rating(50,5));
        $team3->addPlayer($player8, new Rating(30,2));

        $teams = Teams::concat($team1, $team2, $team3);
        $newRatingsWinLose = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2, 2));

        // Winners
        self::assertRating($testClass, 40.877, 3.840, $newRatingsWinLose->getRating($player1));
        self::assertRating($testClass, 45.493, 2.934, $newRatingsWinLose->getRating($player2));
        self::assertRating($testClass, 19.609, 6.396, $newRatingsWinLose->getRating($player3));
        self::assertRating($testClass, 18.712, 5.625, $newRatingsWinLose->getRating($player4));
        self::assertRating($testClass, 29.353, 7.673, $newRatingsWinLose->getRating($player5));
        self::assertRating($testClass, 9.872, 3.891, $newRatingsWinLose->getRating($player6));
        self::assertRating($testClass, 48.830, 4.590, $newRatingsWinLose->getRating($player7));
        self::assertRating($testClass, 29.813, 1.976, $newRatingsWinLose->getRating($player8));

        self::assertMatchQuality($testClass, 0.367, $calculator->calculateMatchQuality($gameInfo, $teams));
    }

    //------------------------------------------------------------------------------
    // Partial Play Tests
    //------------------------------------------------------------------------------

    private static function oneOnTwoBalancedPartialPlay($testClass, SkillCalculator $calculator)
    {
        $gameInfo = new GameInfo();

        $p1 = new Player(1);
        $team1 = new Team($p1, $gameInfo->getDefaultRating());

        $p2 = new Player(2, 0.0);
        $p3 = new Player(3, 1.00);

        $team2 = new Team();
        $team2->addPlayer($p2, $gameInfo->getDefaultRating());
        $team2->addPlayer($p3, $gameInfo->getDefaultRating());

        $teams = Teams::concat($team1, $team2);
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, array(1, 2));

        $p1NewRating = $newRatings->getRating($p1);
        $p2NewRating = $newRatings->getRating($p2);
        $p3NewRating = $newRatings->getRating($p3);

        // This should be roughly the same as a 1 v 1
        self::assertRating($testClass, 29.396480404368411, 7.1713980703143205, $p1NewRating);
        self::assertRating($testClass, 24.999560351959563, 8.3337499787709319, $p2NewRating);
        self::assertRating($testClass, 20.603519595631585, 7.1713980703143205, $p3NewRating);

        $matchQuality = $calculator->calculateMatchQuality($gameInfo, $teams);
        self::assertMatchQuality($testClass, 0.44721358745011336, $matchQuality);
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
