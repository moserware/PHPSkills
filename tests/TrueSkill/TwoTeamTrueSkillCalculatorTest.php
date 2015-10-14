<?php
namespace Skills\Tests\TrueSkill;

use Skills\TrueSkill\TwoTeamTrueSkillCalculator;
use PHPUnit_Framework_TestCase;

class TwoTeamTrueSkillCalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testTwoTeamTrueSkillCalculator()
    {
        $calculator = new TwoTeamTrueSkillCalculator();

        // We only support two players
        //TODO: uncomment testAllTwoPlayerScenarios
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllTwoTeamScenarios($this, $calculator);
    }
}

?>
