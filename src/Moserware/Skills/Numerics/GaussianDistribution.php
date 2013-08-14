<?php

namespace Moserware\Numerics;

require_once(dirname(__FILE__) . "/BasicMath.php");

/**
 * Computes Gaussian (bell curve) values.
 *
 * @author     Jeff Moser <jeff@moserware.com>
 * @copyright  2010 Jeff Moser
 */
class GaussianDistribution
{
    private $_mean;
    private $_standardDeviation;
        
    // precision and precisionMean are used because they make multiplying and dividing simpler
    // (the the accompanying math paper for more details)
    private $_precision;
    private $_precisionMean;
    private $_variance;

    function __construct($mean = 0.0, $standardDeviation = 1.0)
    {   
        $this->_mean = $mean;        
        $this->_standardDeviation = $standardDeviation;
        $this->_variance = square($standardDeviation);

        if($this->_variance != 0)
        {
            $this->_precision = 1.0/$this->_variance;
            $this->_precisionMean = $this->_precision*$this->_mean;
        }
        else
        {
            $this->_precision = \INF;

            if($this->_mean == 0)
            {
                $this->_precisionMean = 0;
            }
            else
            {
                $this->_precisionMean = \INF;
            }
        }
    }    
    
    public function getMean()
    {
        return $this->_mean;
    }
    
    public function getVariance()
    {
        return $this->_variance;
    }
    
    public function getStandardDeviation()
    {
        return $this->_standardDeviation;
    }

    public function getPrecision()
    {
        return $this->_precision;
    }

    public function getPrecisionMean()
    {
        return $this->_precisionMean;
    }
    
    public function getNormalizationConstant()
    {        
        // Great derivation of this is at http://www.astro.psu.edu/~mce/A451_2/A451/downloads/notes0.pdf
        return 1.0/(sqrt(2*M_PI)*$this->_standardDeviation);    
    }
    
    public function __clone()
    {
        $result = new GaussianDistribution();
        $result->_mean = $this->_mean;
        $result->_standardDeviation = $this->_standardDeviation;
        $result->_variance = $this->_variance;
        $result->_precision = $this->_precision;
        $result->_precisionMean = $this->_precisionMean;
        return $result;
    }
    
    public static function fromPrecisionMean($precisionMean, $precision)
    {
        $result = new GaussianDistribution();
        $result->_precision = $precision;
        $result->_precisionMean = $precisionMean;

        if($precision != 0)
        {
            $result->_variance = 1.0/$precision;
            $result->_standardDeviation = sqrt($result->_variance);
            $result->_mean = $result->_precisionMean/$result->_precision;
        }
        else
        {
            $result->_variance = \INF;
            $result->_standardDeviation = \INF;
            $result->_mean = \NAN;
        }
        return $result;
    }
    
    // For details, see http://www.tina-vision.net/tina-knoppix/tina-memo/2003-003.pdf
    // for multiplication, the precision mean ones are easier to write :)
    public static function multiply(GaussianDistribution $left, GaussianDistribution $right)
    {
        return GaussianDistribution::fromPrecisionMean($left->_precisionMean + $right->_precisionMean, $left->_precision + $right->_precision);
    }
    
    // Computes the absolute difference between two Gaussians
    public static function absoluteDifference(GaussianDistribution $left, GaussianDistribution $right)
    {
        return max(
            abs($left->_precisionMean - $right->_precisionMean),
            sqrt(abs($left->_precision - $right->_precision)));
    }

    // Computes the absolute difference between two Gaussians
    public static function subtract(GaussianDistribution $left, GaussianDistribution $right)
    {
        return GaussianDistribution::absoluteDifference($left, $right);
    }
    
    public static function logProductNormalization(GaussianDistribution $left, GaussianDistribution $right)
    {
        if (($left->_precision == 0) || ($right->_precision == 0))
        {
            return 0;
        }

        $varianceSum = $left->_variance + $right->_variance;
        $meanDifference = $left->_mean - $right->_mean;

        $logSqrt2Pi = log(sqrt(2*M_PI));
        return -$logSqrt2Pi - (log($varianceSum)/2.0) - (square($meanDifference)/(2.0*$varianceSum));
    }
    
    public static function divide(GaussianDistribution $numerator, GaussianDistribution $denominator)
    {
        return GaussianDistribution::fromPrecisionMean($numerator->_precisionMean - $denominator->_precisionMean,
                                 $numerator->_precision - $denominator->_precision);
    }

    public static function logRatioNormalization(GaussianDistribution $numerator, GaussianDistribution $denominator)
    {
        if (($numerator->_precision == 0) || ($denominator->_precision == 0))
        {
            return 0;
        }

        $varianceDifference = $denominator->_variance - $numerator->_variance;
        $meanDifference = $numerator->_mean - $denominator->_mean;

        $logSqrt2Pi = log(sqrt(2*M_PI));

        return log($denominator->_variance) + $logSqrt2Pi - log($varianceDifference)/2.0 +
               square($meanDifference)/(2*$varianceDifference);
    }
    
    public static function at($x, $mean = 0.0, $standardDeviation = 1.0)
    {
        // See http://mathworld.wolfram.com/NormalDistribution.html
        //                1              -(x-mean)^2 / (2*stdDev^2)
        // P(x) = ------------------- * e
        //        stdDev * sqrt(2*pi)

        $multiplier = 1.0/($standardDeviation*sqrt(2*M_PI));
        $expPart = exp((-1.0*square($x - $mean))/(2*square($standardDeviation)));
        $result = $multiplier*$expPart;
        return $result;
    }

    public static function cumulativeTo($x, $mean = 0.0, $standardDeviation = 1.0)
    {
        $invsqrt2 = -0.707106781186547524400844362104;
        $result = GaussianDistribution::errorFunctionCumulativeTo($invsqrt2*$x);
        return 0.5*$result;
    }
    
    private static function errorFunctionCumulativeTo($x)
    {
        // Derived from page 265 of Numerical Recipes 3rd Edition            
        $z = abs($x);

        $t = 2.0/(2.0 + $z);
        $ty = 4*$t - 2;

        $coefficients = array(
                                -1.3026537197817094, 
                                6.4196979235649026e-1,
                                1.9476473204185836e-2, 
                                -9.561514786808631e-3, 
                                -9.46595344482036e-4,
                                3.66839497852761e-4, 
                                4.2523324806907e-5, 
                                -2.0278578112534e-5,
                                -1.624290004647e-6, 
                                1.303655835580e-6, 
                                1.5626441722e-8, 
                                -8.5238095915e-8,
                                6.529054439e-9, 
                                5.059343495e-9, 
                                -9.91364156e-10, 
                                -2.27365122e-10,
                                9.6467911e-11, 
                                2.394038e-12, 
                                -6.886027e-12, 
                                8.94487e-13, 
                                3.13092e-13,
                                -1.12708e-13, 
                                3.81e-16, 
                                7.106e-15, 
                                -1.523e-15, 
                                -9.4e-17, 
                                1.21e-16, 
                                -2.8e-17 );

        $ncof = count($coefficients);
        $d = 0.0;
        $dd = 0.0;

        for ($j = $ncof - 1; $j > 0; $j--)
        {
            $tmp = $d;
            $d = $ty*$d - $dd + $coefficients[$j];
            $dd = $tmp;
        }

        $ans = $t*exp(-$z*$z + 0.5*($coefficients[0] + $ty*$d) - $dd);
        return ($x >= 0.0) ? $ans : (2.0 - $ans);
    }
    
    private static function inverseErrorFunctionCumulativeTo($p)
    {
        // From page 265 of numerical recipes                       

        if ($p >= 2.0)
        {
            return -100;
        }
        if ($p <= 0.0)
        {
            return 100;
        }

        $pp = ($p < 1.0) ? $p : 2 - $p;
        $t = sqrt(-2*log($pp/2.0)); // Initial guess
        $x = -0.70711*((2.30753 + $t*0.27061)/(1.0 + $t*(0.99229 + $t*0.04481)) - $t);

        for ($j = 0; $j < 2; $j++)
        {
            $err = GaussianDistribution::errorFunctionCumulativeTo($x) - $pp;
            $x += $err/(1.12837916709551257*exp(-square($x)) - $x*$err); // Halley                
        }

        return ($p < 1.0) ? $x : -$x;
    }

    public static function inverseCumulativeTo($x, $mean = 0.0, $standardDeviation = 1.0)
    {
        // From numerical recipes, page 320
        return $mean - sqrt(2)*$standardDeviation*GaussianDistribution::inverseErrorFunctionCumulativeTo(2*$x);
    }
    
    public function __toString()
    {
        return sprintf("mean=%.4f standardDeviation=%.4f", $this->_mean, $this->_standardDeviation);
    }  
}
?>