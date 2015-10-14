<?php
namespace Skills\Tests\TrueSkill;

use Skills\TrueSkill\TwoPlayerTrueSkillCalculator;
use PHPUnit_Framework_TestCase;

class TwoPlayerTrueSkillCalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testTwoPlayerTrueSkillCalculator()
    {
        $calculator = new TwoPlayerTrueSkillCalculator();

        // We only support two players
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
    }
}
?>
