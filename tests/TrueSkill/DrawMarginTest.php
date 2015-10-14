<?php
namespace Skills\Tests\TrueSkill;

use PHPUnit_Framework_TestCase;
use Skills\TrueSkill\DrawMargin;

class DrawMarginTest extends PHPUnit_Framework_TestCase
{    
    const ERROR_TOLERANCE = 0.000001;
    
    public function testGetDrawMarginFromDrawProbability()
    {
        $beta = 25.0 / 6.0;
        // The expected values were compared against Ralf Herbrich's implementation in F#
        $this->assertDrawMargin(0.10, $beta, 0.74046637542690541);
        $this->assertDrawMargin(0.25, $beta, 1.87760059883033);
        $this->assertDrawMargin(0.33, $beta, 2.5111010132487492);
    }

    private function assertDrawMargin($drawProbability, $beta, $expected)
    {
        $actual = DrawMargin::getDrawMarginFromDrawProbability($drawProbability, $beta);
        $this->assertEquals($expected, $actual, '', DrawMarginTest::ERROR_TOLERANCE);
    }    
}
?>

