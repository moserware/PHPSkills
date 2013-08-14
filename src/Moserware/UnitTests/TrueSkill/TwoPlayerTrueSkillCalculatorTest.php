<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once(dirname(__FILE__) . '/../../Skills/TrueSkill/TwoPlayerTrueSkillCalculator.php');
require_once(dirname(__FILE__) . '/TrueSkillCalculatorTests.php');

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

$testSuite = new \PHPUnit_Framework_TestSuite();
$testSuite->addTest( new TwoPlayerTrueSkillCalculatorTest("testTwoPlayerTrueSkillCalculator"));

\PHPUnit_TextUI_TestRunner::run($testSuite);
?>
