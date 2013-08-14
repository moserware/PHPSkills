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
 * Supplies the factor graph with prior information.
 *
 * See the accompanying math paper for more details.
 */
class GaussianPriorFactor extends GaussianFactor
{
    private $_newMessage;

    public function __construct($mean, $variance, Variable $variable)
    {
        parent::__construct(sprintf("Prior value going to %s", $variable));
        $this->_newMessage = new GaussianDistribution($mean, sqrt($variance));
        $newMessage = new Message(GaussianDistribution::fromPrecisionMean(0, 0),
                                  sprintf("message from %s to %s", $this, $variable));

        $this->createVariableToMessageBindingWithMessage($variable, $newMessage);
    }

    protected function updateMessageVariable(Message $message, Variable $variable)
    {
        $oldMarginal = clone $variable->getValue();
        $oldMessage = $message;
        $newMarginal =
            GaussianDistribution::fromPrecisionMean(
                $oldMarginal->getPrecisionMean() + $this->_newMessage->getPrecisionMean() - $oldMessage->getValue()->getPrecisionMean(),
                $oldMarginal->getPrecision() + $this->_newMessage->getPrecision() - $oldMessage->getValue()->getPrecision());

        $variable->setValue($newMarginal);
        $newMessage = $this->_newMessage;
        $message->setValue($newMessage);
        return GaussianDistribution::subtract($oldMarginal, $newMarginal);
    }
}

?>
