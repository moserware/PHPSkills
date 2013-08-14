<?php
namespace Moserware\Skills\TrueSkill\Factors;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Message.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Variable.php");
require_once(dirname(__FILE__) . "/../../Numerics/GaussianDistribution.php");
require_once(dirname(__FILE__) . "/../TruncatedGaussianCorrectionFunctions.php");
require_once(dirname(__FILE__) . "/GaussianFactor.php");

use Moserware\Numerics\GaussianDistribution;
use Moserware\Skills\TrueSkill\TruncatedGaussianCorrectionFunctions;
use Moserware\Skills\FactorGraphs\Message;
use Moserware\Skills\FactorGraphs\Variable;

/**
 * Factor representing a team difference that has exceeded the draw margin.
 *
 * See the accompanying math paper for more details.
 */
class GaussianGreaterThanFactor extends GaussianFactor
{
    private $_epsilon;

    public function __construct($epsilon, Variable $variable)
    {
        parent::__construct(\sprintf("%s > %.2f", $variable, $epsilon));
        $this->_epsilon = $epsilon;
        $this->createVariableToMessageBinding($variable);
    }

    public function getLogNormalization()
    {
        $vars = $this->getVariables();
        $marginal = $vars[0]->getValue();
        $messages = $this->getMessages();
        $message = $messages[0]->getValue();
        $messageFromVariable = GaussianDistribution::divide($marginal, $message);
        return -GaussianDistribution::logProductNormalization($messageFromVariable, $message)
               +
               log(
                   GaussianDistribution::cumulativeTo(($messageFromVariable->getMean() - $this->_epsilon)/
                                                     $messageFromVariable->getStandardDeviation()));

    }

    protected function updateMessageVariable(Message $message, Variable $variable)
    {
        $oldMarginal = clone $variable->getValue();
        $oldMessage = clone $message->getValue();
        $messageFromVar = GaussianDistribution::divide($oldMarginal, $oldMessage);

        $c = $messageFromVar->getPrecision();
        $d = $messageFromVar->getPrecisionMean();

        $sqrtC = sqrt($c);

        $dOnSqrtC = $d/$sqrtC;

        $epsilsonTimesSqrtC = $this->_epsilon*$sqrtC;
        $d = $messageFromVar->getPrecisionMean();

        $denom = 1.0 - TruncatedGaussianCorrectionFunctions::wExceedsMargin($dOnSqrtC, $epsilsonTimesSqrtC);

        $newPrecision = $c/$denom;
        $newPrecisionMean = ($d +
                             $sqrtC*
                             TruncatedGaussianCorrectionFunctions::vExceedsMargin($dOnSqrtC, $epsilsonTimesSqrtC))/
                             $denom;

        $newMarginal = GaussianDistribution::fromPrecisionMean($newPrecisionMean, $newPrecision);

        $newMessage = GaussianDistribution::divide(
                              GaussianDistribution::multiply($oldMessage, $newMarginal),
                              $oldMarginal);

        // Update the message and marginal
        $message->setValue($newMessage);

        $variable->setValue($newMarginal);

        // Return the difference in the new marginal
        return GaussianDistribution::subtract($newMarginal, $oldMarginal);
    }
}
?>
