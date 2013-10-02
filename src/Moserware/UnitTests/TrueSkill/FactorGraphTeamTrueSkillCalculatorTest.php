<?php
namespace Moserware\UnitTests\TrueSkill;

use \PHPUnit_Framework_TestCase;
use Moserware\Skills\TrueSkill\FactorGraphTrueSkillCalculator;

class FactorGraphTrueSkillCalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testFactorGraphTrueSkillCalculator()
    {     
        $calculator = new FactorGraphTrueSkillCalculator();
        
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllTwoTeamScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllMultipleTeamScenarios($this, $calculator);
        TrueSkillCalculatorTests::testPartialPlayScenarios($this, $calculator);
    }
}
?>
