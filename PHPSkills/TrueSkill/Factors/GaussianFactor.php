<?php

namespace Moserware\Skills\TrueSkill\Factors;

abstract class GaussianFactor extends Factor
{
    protected function __construct($name)
    {
        parent::__construct($name);
    }

    /// Sends the factor-graph message with and returns the log-normalization constant
    protected function sendMessageVariable(Message $message, Variable $variable)
    {
        $marginal = $variable->getValue();
        $messageValue = $message->getValue();
        $logZ = GaussianDistribution::logProductNormalization($marginal, $messageValue);
        $variable->setValue($marginal*$messageValue);
        return $logZ;
    }

    public function createVariableToMessageBinding(Variable $variable)
    {
        return parent::createVariableToMessageBinding($variable,
                                                      new Message(
                                                          GaussianDistribution::fromPrecisionMean(0, 0),
                                                          "message from {0} to {1}", $this));
    }
}


?>
