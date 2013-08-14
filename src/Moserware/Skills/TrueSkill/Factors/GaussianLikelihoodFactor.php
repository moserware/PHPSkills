<?php
namespace Moserware\Skills\TrueSkill\Factors;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Message.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Variable.php");
require_once(dirname(__FILE__) . "/../../Numerics/GaussianDistribution.php");
require_once(dirname(__FILE__) . "/GaussianFactor.php");

use Moserware\Numerics\GaussianDistribution;
use Moserware\Skills\FactorGraphs\Message;
use Moserware\Skills\FactorGraphs\Variable;

/**
 * Connects two variables and adds uncertainty.
 * 
 * See the accompanying math paper for more details.
 */
class GaussianLikelihoodFactor extends GaussianFactor
{
    private $_precision;

    public function __construct($betaSquared, Variable $variable1, Variable $variable2)
    {
        parent::__construct(sprintf("Likelihood of %s going to %s", $variable2, $variable1));
        $this->_precision = 1.0/$betaSquared;
        $this->createVariableToMessageBinding($variable1);
        $this->createVariableToMessageBinding($variable2);
    }

    public function getLogNormalization()
    {
        $vars = $this->getVariables();
        $messages = $this->getMessages();

        return GaussianDistribution::logRatioNormalization(
                $vars[0]->getValue(),
                $messages[0]->getValue());
    }

    private function updateHelper(Message $message1, Message $message2,
                                  Variable $variable1, Variable $variable2)
    {        
        $message1Value = clone $message1->getValue();
        $message2Value = clone $message2->getValue();        
        
        $marginal1 = clone $variable1->getValue();
        $marginal2 = clone $variable2->getValue();

        $a = $this->_precision/($this->_precision + $marginal2->getPrecision() - $message2Value->getPrecision());

        $newMessage = GaussianDistribution::fromPrecisionMean(
            $a*($marginal2->getPrecisionMean() - $message2Value->getPrecisionMean()),
            $a*($marginal2->getPrecision() - $message2Value->getPrecision()));

        $oldMarginalWithoutMessage = GaussianDistribution::divide($marginal1, $message1Value);

        $newMarginal = GaussianDistribution::multiply($oldMarginalWithoutMessage, $newMessage);

        // Update the message and marginal

        $message1->setValue($newMessage);
        $variable1->setValue($newMarginal);

        // Return the difference in the new marginal
        return GaussianDistribution::subtract($newMarginal, $marginal1);
    }

    public function updateMessageIndex($messageIndex)
    {
        $messages = $this->getMessages();
        $vars = $this->getVariables();

        switch ($messageIndex)
        {
            case 0:
                return $this->updateHelper($messages[0], $messages[1],
                                           $vars[0], $vars[1]);
            case 1:
                return $this->updateHelper($messages[1], $messages[0],
                                           $vars[1], $vars[0]);
            default:
                throw new Exception();
        }
    }
}

?>
