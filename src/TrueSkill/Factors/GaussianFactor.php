<?php

namespace Skills\TrueSkill\Factors;

use Skills\Numerics\GaussianDistribution;
use Skills\FactorGraphs\Factor;
use Skills\FactorGraphs\Message;
use Skills\FactorGraphs\Variable;

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
