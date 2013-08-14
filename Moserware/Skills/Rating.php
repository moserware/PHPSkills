<?php

namespace Moserware\Skills;

// Container for a player's rating.
class Rating 
{
    const CONSERVATIVE_STANDARD_DEVIATION_MULTIPLIER = 3;
    
    private $_conservativeStandardDeviationMultiplier;
    private $_mean;
    private $_standardDeviation;

    /**
     * Constructs a rating.
     * @param double $mean The statistical mean value of the rating (also known as mu).
     * @param double $standardDeviation The standard deviation of the rating (also known as s).
     * @param double $conservativeStandardDeviationMultiplier optional The number of standardDeviations to subtract from the mean to achieve a conservative rating.
     */
    public function __construct($mean, $standardDeviation, $conservativeStandardDeviationMultiplier = self::CONSERVATIVE_STANDARD_DEVIATION_MULTIPLIER)
    {
        $this->_mean = $mean;
        $this->_standardDeviation = $standardDeviation;
        $this->_conservativeStandardDeviationMultiplier = $conservativeStandardDeviationMultiplier;
    }

    /**
     * The statistical mean value of the rating (also known as �).
     */
    public function getMean()
    {
        return $this->_mean;        
    }

    /**
     * The standard deviation (the spread) of the rating. This is also known as s.
     */
    public function getStandardDeviation()
    {
        return $this->_standardDeviation;
    }

    /**
     * A conservative estimate of skill based on the mean and standard deviation.
     */
    public function getConservativeRating()
    {
        return $this->_mean - $this->_conservativeStandardDeviationMultiplier*$this->_standardDeviation;
    }

    public function getPartialUpdate(Rating $prior, Rating $fullPosterior, $updatePercentage)
    {
        $priorGaussian = new GaussianDistribution($prior->getMean(), $prior->getStandardDeviation());
        $posteriorGaussian = new GaussianDistribution($fullPosterior->getMean(), $fullPosterior.getStandardDeviation());

        // From a clarification email from Ralf Herbrich:
        // "the idea is to compute a linear interpolation between the prior and posterior skills of each player 
        //  ... in the canonical space of parameters"

        $precisionDifference = $posteriorGaussian->getPrecision() - $priorGaussian->getPrecision();
        $partialPrecisionDifference = $updatePercentage*$precisionDifference;

        $precisionMeanDifference = $posteriorGaussian->getPrecisionMean() - $priorGaussian.getPrecisionMean();
        $partialPrecisionMeanDifference = $updatePercentage*$precisionMeanDifference;

        $partialPosteriorGaussion = GaussianDistribution::fromPrecisionMean(
            $priorGaussian->getPrecisionMean() + $partialPrecisionMeanDifference,
            $priorGaussian->getPrecision() + $partialPrecisionDifference);

        return new Rating($partialPosteriorGaussion->getMean(), $partialPosteriorGaussion->getStandardDeviation(), $prior->_conservativeStandardDeviationMultiplier);
    }

    public function __toString()
    {
        return sprintf("mean=%.4f, standardDeviation=%.4f", $this->_mean, $this->_standardDeviation);
    }  
}

?>