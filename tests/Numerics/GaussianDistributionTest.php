<?php
namespace Moserware\Numerics;

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once(dirname(__FILE__) . '/../../Skills/Numerics/GaussianDistribution.php');


use \PHPUnit_Framework_TestCase;
 
class GaussianDistributionTest extends PHPUnit_Framework_TestCase
{    
    const ERROR_TOLERANCE = 0.000001;
    
    public function testCumulativeTo()
    {    
        // Verified with WolframAlpha
        // (e.g. http://www.wolframalpha.com/input/?i=CDF%5BNormalDistribution%5B0%2C1%5D%2C+0.5%5D )
        $this->assertEquals( 0.691462, GaussianDistribution::cumulativeTo(0.5),'', GaussianDistributionTest::ERROR_TOLERANCE);            
    }
    
    public function testAt()
    {
        // Verified with WolframAlpha
        // (e.g. http://www.wolframalpha.com/input/?i=PDF%5BNormalDistribution%5B0%2C1%5D%2C+0.5%5D )
        $this->assertEquals(0.352065, GaussianDistribution::at(0.5), '', GaussianDistributionTest::ERROR_TOLERANCE);
    }
    
    public function testMultiplication()
    {
        // I verified this against the formula at http://www.tina-vision.net/tina-knoppix/tina-memo/2003-003.pdf
        $standardNormal = new GaussianDistribution(0, 1);        
        $shiftedGaussian = new GaussianDistribution(2, 3);
        $product = GaussianDistribution::multiply($standardNormal, $shiftedGaussian);
        
        $this->assertEquals(0.2, $product->getMean(), '', GaussianDistributionTest::ERROR_TOLERANCE);
        $this->assertEquals(3.0 / sqrt(10), $product->getStandardDeviation(), '', GaussianDistributionTest::ERROR_TOLERANCE);

        $m4s5 = new GaussianDistribution(4, 5);
        $m6s7 = new GaussianDistribution(6, 7);

        $product2 = GaussianDistribution::multiply($m4s5, $m6s7);
        
        $expectedMean = (4 * square(7) + 6 * square(5)) / (square(5) + square(7));
        $this->assertEquals($expectedMean, $product2->getMean(), '', GaussianDistributionTest::ERROR_TOLERANCE);

        $expectedSigma = sqrt(((square(5) * square(7)) / (square(5) + square(7))));
        $this->assertEquals($expectedSigma, $product2->getStandardDeviation(), '', GaussianDistributionTest::ERROR_TOLERANCE);
    }
    
    public function testDivision()
    {
        // Since the multiplication was worked out by hand, we use the same numbers but work backwards
        $product = new GaussianDistribution(0.2, 3.0 / sqrt(10));
        $standardNormal = new GaussianDistribution(0, 1);

        $productDividedByStandardNormal = GaussianDistribution::divide($product, $standardNormal);
        $this->assertEquals(2.0, $productDividedByStandardNormal->getMean(), '', GaussianDistributionTest::ERROR_TOLERANCE);
        $this->assertEquals(3.0, $productDividedByStandardNormal->getStandardDeviation(),'', GaussianDistributionTest::ERROR_TOLERANCE);
        
        $product2 = new GaussianDistribution((4 * square(7) + 6 * square(5)) / (square(5) + square(7)), sqrt(((square(5) * square(7)) / (square(5) + square(7)))));
        $m4s5 = new GaussianDistribution(4,5);
        $product2DividedByM4S5 = GaussianDistribution::divide($product2, $m4s5);
        $this->assertEquals(6.0, $product2DividedByM4S5->getMean(), '', GaussianDistributionTest::ERROR_TOLERANCE);
        $this->assertEquals(7.0, $product2DividedByM4S5->getStandardDeviation(), '', GaussianDistributionTest::ERROR_TOLERANCE);
    }
    
    public function testLogProductNormalization()
    {
        // Verified with Ralf Herbrich's F# implementation
        $standardNormal = new GaussianDistribution(0, 1);
        $lpn = GaussianDistribution::logProductNormalization($standardNormal, $standardNormal);
        $this->assertEquals(-1.2655121234846454, $lpn, '', GaussianDistributionTest::ERROR_TOLERANCE);

        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $lpn2 = GaussianDistribution::logProductNormalization($m1s2, $m3s4);
        $this->assertEquals(-2.5168046699816684, $lpn2, '', GaussianDistributionTest::ERROR_TOLERANCE);
    }
    
    public function testLogRatioNormalization()
    {
        // Verified with Ralf Herbrich's F# implementation            
        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $lrn = GaussianDistribution::logRatioNormalization($m1s2, $m3s4);
        $this->assertEquals(2.6157405972171204, $lrn, '', GaussianDistributionTest::ERROR_TOLERANCE);            
    }
    
    public function testAbsoluteDifference()
    {
        // Verified with Ralf Herbrich's F# implementation            
        $standardNormal = new GaussianDistribution(0, 1);
        $absDiff = GaussianDistribution::absoluteDifference($standardNormal, $standardNormal);
        $this->assertEquals(0.0, $absDiff, '', GaussianDistributionTest::ERROR_TOLERANCE);

        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $absDiff2 = GaussianDistribution::absoluteDifference($m1s2, $m3s4);
        $this->assertEquals(0.4330127018922193, $absDiff2, '', GaussianDistributionTest::ERROR_TOLERANCE);
    }
}

?>

