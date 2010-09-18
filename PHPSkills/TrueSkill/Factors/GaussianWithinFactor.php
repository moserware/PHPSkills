<?php
namespace Moserware\Skills\TrueSkill\Factors;

require_once(dirname(__FILE__) . "GaussianFactor.php");
require_once(dirname(__FILE__) . "../TruncatedGaussianCorrectionFunctions.php");
require_once(dirname(__FILE__) . "../../FactorGraphs/Message.php");
require_once(dirname(__FILE__) . "../../FactorGraphs/Variable.php");
require_once(dirname(__FILE__) . "../../Numerics/GaussianDistribution.php");

use Moserware\Numerics\GaussianDistribution;
use Moserware\Skills\TrueSkill\TruncatedGaussianCorrectionFunctions;
use Moserware\Skills\FactorGraphs\Message;
use Moserware\Skills\FactorGraphs\Variable;

/// <summary>
/// Factor representing a team difference that has not exceeded the draw margin.
/// </summary>
/// <remarks>See the accompanying math paper for more details.</remarks>
class GaussianWithinFactor extends GaussianFactor
{
    private $_epsilon;

    public function __construct($epsilon, Variable $variable)
    {
        $this->_epsilon = $epsilon;
        $this->createVariableToMessageBinding($variable);
    }

    public function getLogNormalization()
    {
        $variables = $this->getVariables();
        $marginal = $variables[0]->getValue();

        $messages = $this->getMessages();
        $message = $messages[0]->getValue();
        $messageFromVariable = GaussianDistribution::divide($marginal, $message);
        $mean = $messageFromVariable->getMean();
        $std = $messageFromVariable->getStandardDeviation();
        $z = GaussianDistribution::cumulativeTo(($this->_epsilon - $mean)/$std)
             -
             GaussianDistribution::cumulativeTo((-$this->_epsilon - $mean)/$std);

        return -GaussianDistribution::logProductNormalization($messageFromVariable, $message) + log($z);
    }

    protected function updateMessage(Message $message, Variable $variable)
    {
        $oldMarginal = clone $variable->getValue();
        $oldMessage = clone $message->getValue();
        $messageFromVariable = GaussianDistribution::divide($oldMarginal, $oldMessage);

        $c = $messageFromVariable->getPrecision();
        $d = $messageFromVariable->getPrecisionMean();

        $sqrtC = sqrt($c);
        $dOnSqrtC = $d/$sqrtC;

        $epsilonTimesSqrtC = $this->_epsilon*$sqrtC;
        $d = $messageFromVariable->getPrecisionMean();

        $denominator = 1.0 - TruncatedGaussianCorrectionFunctions::wWithinMargin($dOnSqrtC, $epsilonTimesSqrtC);
        $newPrecision = $c/$denominator;
        $newPrecisionMean = ($d +
                                   $sqrtC*
                                   TruncatedGaussianCorrectionFunctions::vWithinMargin($dOnSqrtC, $epsilonTimesSqrtC))/
                                  $denominator;

        $newMarginal = GaussianDistribution::fromPrecisionMean($newPrecisionMean, $newPrecision);
        $newMessage = GaussianDistribution::divide(
                        GaussianDistribution::multiply($oldMessage, $newMarginal),
                        $oldMarginal);

        /// Update the message and marginal
        $message->setValue($newMessage);
        $variable->setValue($newMarginal);

        /// Return the difference in the new marginal
        return GaussianDistribution::subtract($newMarginal, $oldMarginal);
    }
}

?>
