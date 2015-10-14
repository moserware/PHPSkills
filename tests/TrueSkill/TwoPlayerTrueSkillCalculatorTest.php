<?php
namespace Skills\Tests\TrueSkill;

use Skills\TrueSkill\TwoPlayerTrueSkillCalculator;

class TwoPlayerTrueSkillCalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testTwoPlayerTrueSkillCalculator()
    {
        $calculator = new TwoPlayerTrueSkillCalculator();

        // We only support two players
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
    }
}

$testSuite = new \PHPUnit_Framework_TestSuite();
$testSuite->addTest( new TwoPlayerTrueSkillCalculatorTest("testTwoPlayerTrueSkillCalculator"));

\PHPUnit_TextUI_TestRunner::run($testSuite);
?>
