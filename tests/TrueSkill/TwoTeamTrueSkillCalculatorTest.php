<?php namespace Moserware\Skills\Tests\TrueSkill;

use Moserware\Skills\Tests\TestCase;
use Moserware\Skills\TrueSkill\TwoTeamTrueSkillCalculator;

class TwoTeamTrueSkillCalculatorTest extends TestCase
{
    public function testTwoTeamTrueSkillCalculator()
    {
        $calculator = new TwoTeamTrueSkillCalculator();

        // We only support two players
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllTwoTeamScenarios($this, $calculator);
    }
}