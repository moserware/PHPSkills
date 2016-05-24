<?php namespace Moserware\Skills\Tests\TrueSkill;

use Moserware\Skills\Tests\TestCase;
use Moserware\Skills\TrueSkill\FactorGraphTrueSkillCalculator;

class FactorGraphTrueSkillCalculatorTest extends TestCase
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