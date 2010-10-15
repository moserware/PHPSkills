<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once(dirname(__FILE__) . '/../../Skills/TrueSkill/TwoTeamTrueSkillCalculator.php');
require_once(dirname(__FILE__) . '/TrueSkillCalculatorTests.php');

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

$testSuite = new \PHPUnit_Framework_TestSuite();
$testSuite->addTest( new TwoTeamTrueSkillCalculatorTest("testTwoTeamTrueSkillCalculator"));

\PHPUnit_TextUI_TestRunner::run($testSuite);
?>
