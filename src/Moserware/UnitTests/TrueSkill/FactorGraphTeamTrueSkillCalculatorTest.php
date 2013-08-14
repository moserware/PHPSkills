<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once(dirname(__FILE__) . '/../../Skills/TrueSkill/FactorGraphTrueSkillCalculator.php');
require_once(dirname(__FILE__) . '/TrueSkillCalculatorTests.php');

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

$testSuite = new \PHPUnit_Framework_TestSuite();
$testSuite->addTest( new FactorGraphTrueSkillCalculatorTest("testFactorGraphTrueSkillCalculator"));

\PHPUnit_TextUI_TestRunner::run($testSuite);
?>
