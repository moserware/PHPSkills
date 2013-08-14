<?php
namespace Moserware\Skills\FactorGraphs;

require_once(dirname(__FILE__) . "/../Guard.php");
require_once(dirname(__FILE__) . "/../HashMap.php");
require_once(dirname(__FILE__) . "/Message.php");
require_once(dirname(__FILE__) . "/Variable.php");

use Moserware\Skills\Guard;
use Moserware\Skills\HashMap;

abstract class Factor
{
    private $_messages = array();
    private $_messageToVariableBinding;

    private $_name;
    private $_variables = array();

    protected function __construct($name)
    {
        $this->_name = "Factor[" . $name . "]";
        $this->_messageToVariableBinding = new HashMap();
    }

    /**
     * @return The log-normalization constant of that factor
     */
    public function getLogNormalization()
    {
        return 0;
    }

    /**
     * @return The number of messages that the factor has
     */
    public function getNumberOfMessages()
    {
        return count($this->_messages);
    }
    
    protected function getVariables()
    {
        return $this->_variables;
    }
    
    protected function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Update the message and marginal of the i-th variable that the factor is connected to
     */
    public function updateMessageIndex($messageIndex)
    {
        Guard::argumentIsValidIndex($messageIndex, count($this->_messages), "messageIndex");
        $message = $this->_messages[$messageIndex];
        $variable = $this->_messageToVariableBinding->getValue($message);
        return $this->updateMessageVariable($message, $variable);
    }

    protected function updateMessageVariable(Message $message, Variable $variable)
    {
        throw new Exception();
    }

    /**
     * Resets the marginal of the variables a factor is connected to
     */
    public function resetMarginals()
    {
        $allValues = $this->_messageToVariableBinding->getAllValues();
        foreach ($allValues as $currentVariable)
        {
            $currentVariable->resetToPrior();
        }
    }

    /**
     * Sends the ith message to the marginal and returns the log-normalization constant
     */
    public function sendMessageIndex($messageIndex)
    {
        Guard::argumentIsValidIndex($messageIndex, count($this->_messages), "messageIndex");

        $message = $this->_messages[$messageIndex];
        $variable = $this->_messageToVariableBinding->getValue($message);
        return $this->sendMessageVariable($message, $variable);
    }

    protected abstract function sendMessageVariable(Message $message, Variable $variable);

    public abstract function createVariableToMessageBinding(Variable $variable);

    protected function createVariableToMessageBindingWithMessage(Variable $variable, Message $message)
    {
        $index = count($this->_messages);
        $localMessages = $this->_messages;
        $localMessages[] = $message;
        $this->_messageToVariableBinding->setValue($message, $variable);
        $localVariables = $this->_variables;
        $localVariables[] = $variable;
        return $message;
    }

    public function __toString()
    {
        return ($this->_name != null) ? $this->_name : base::__toString();
    }
}

?>
