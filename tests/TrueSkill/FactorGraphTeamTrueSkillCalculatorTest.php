<?php
namespace Skills\Tests\TrueSkill;

use Skills\TrueSkill\FactorGraphTrueSkillCalculator;
use PHPUnit_Framework_TestCase;

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
