<?php
namespace Skills\Tests\TrueSkill;

use Skills\TrueSkill\TwoTeamTrueSkillCalculator;
use Skills\Tests\TrueSkill\TrueSkillCalculatorTests;
use PHPUnit_Framework_TestCase;
use PHPUnit_TextUI_TestRunner;

class TwoTeamTrueSkillCalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testTwoTeamTrueSkillCalculator()
    {
        $calculator = new TwoTeamTrueSkillCalculator();

        // We only support two players
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllTwoTeamScenarios($this, $calculator);
    }
}

$testSuite = new \PHPUnit_Framework_TestSuite();
$testSuite->addTest( new TwoTeamTrueSkillCalculatorTest("testTwoTeamTrueSkillCalculator"));

PHPUnit_TextUI_TestRunner::run($testSuite);
?>
