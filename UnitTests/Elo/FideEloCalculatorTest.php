<?php
namespace Moserware\Skills\Elo;

require_once(dirname(__FILE__) . '/EloAssert.php');
require_once(dirname(__FILE__) . '/../../Skills/PairwiseComparison.php');
require_once(dirname(__FILE__) . '/../../Skills/Elo/FideEloCalculator.php');
require_once(dirname(__FILE__) . '/../../Skills/Elo/FideKFactor.php');

use Moserware\Skills\PairwiseComparison;
use \PHPUnit_Framework_TestCase;
 
class FideEloCalculatorTest extends PHPUnit_Framework_TestCase
{       
    public function testFideProvisionalEloCalculator()
    {
        // verified against http://ratings.fide.com/calculator_rtd.phtml
        $calc = new FideEloCalculator(new ProvisionalFideKFactor());
        
        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::WIN, 1221.25, 1478.75);
        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::DRAW, 1208.75, 1491.25);
        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::LOSE, 1196.25, 1503.75);
    }

    public function testFideNonProvisionalEloCalculator()
    {
        // verified against http://ratings.fide.com/calculator_rtd.phtml
        $calc = FideEloCalculator::createWithDefaultKFactor();

        EloAssert::assertChessRating($this, $calc, 1200, 1200, PairwiseComparison::WIN, 1207.5, 1192.5);
        EloAssert::assertChessRating($this, $calc, 1200, 1200, PairwiseComparison::DRAW, 1200, 1200);
        EloAssert::assertChessRating($this, $calc, 1200, 1200, PairwiseComparison::LOSE, 1192.5, 1207.5);

        EloAssert::assertChessRating($this, $calc, 2600, 2500, PairwiseComparison::WIN, 2603.6, 2496.4);
        EloAssert::assertChessRating($this, $calc, 2600, 2500, PairwiseComparison::DRAW, 2598.6, 2501.4);
        EloAssert::assertChessRating($this, $calc, 2600, 2500, PairwiseComparison::LOSE, 2593.6, 2506.4);
    }
}
?>

