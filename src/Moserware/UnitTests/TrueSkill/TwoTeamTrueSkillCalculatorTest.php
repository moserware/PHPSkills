<?php
namespace Moserware\UnitTests\TrueSkill;

use \PHPUnit_Framework_TestCase;
use Moserware\Skills\TrueSkill\TwoTeamTrueSkillCalculator;

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
?>
