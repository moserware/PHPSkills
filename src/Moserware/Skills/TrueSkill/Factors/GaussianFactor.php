<?php

namespace Moserware\Skills\TrueSkill\Factors;

require_once(dirname(__FILE__) . "/../../FactorGraphs/Factor.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Message.php");
require_once(dirname(__FILE__) . "/../../FactorGraphs/Variable.php");
require_once(dirname(__FILE__) . "/../../Numerics/GaussianDistribution.php");

use Moserware\Numerics\GaussianDistribution;
use Moserware\Skills\FactorGraphs\Factor;
use Moserware\Skills\FactorGraphs\Message;
use Moserware\Skills\FactorGraphs\Variable;

abstract class GaussianFactor extends Factor
{
    protected function __construct($name)
    {
        parent::__construct($name);
    }

    /**
     * Sends the factor-graph message with and returns the log-normalization constant.
     */
    protected function sendMessageVariable(Message $message, Variable $variable)
    {
        $marginal = $variable->getValue();
        $messageValue = $message->getValue();
        $logZ = GaussianDistribution::logProductNormalization($marginal, $messageValue);
        $variable->setValue(GaussianDistribution::multiply($marginal, $messageValue));
        return $logZ;
    }

    public function createVariableToMessageBinding(Variable $variable)
    {
        $newDistribution = GaussianDistribution::fromPrecisionMean(0, 0);
        $binding = parent::createVariableToMessageBindingWithMessage($variable,
                                                      new Message(
                                                          $newDistribution,
                                                          sprintf("message from %s to %s", $this, $variable)));
        return $binding;
    }
}

?>
