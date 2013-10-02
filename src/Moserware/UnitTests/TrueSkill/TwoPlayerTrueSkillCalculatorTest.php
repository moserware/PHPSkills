<?php
namespace Moserware\UnitTests\TrueSkill;

use \PHPUnit_Framework_TestCase;
use Moserware\Skills\TrueSkill\TwoPlayerTrueSkillCalculator;

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
