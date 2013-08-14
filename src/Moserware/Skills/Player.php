<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . "/Guard.php");
require_once(dirname(__FILE__) . "/ISupportPartialPlay.php");
require_once(dirname(__FILE__) . "/ISupportPartialUpdate.php");

/**
 * Represents a player who has a Rating.
 */
class Player implements ISupportPartialPlay, ISupportPartialUpdate
{
    const DEFAULT_PARTIAL_PLAY_PERCENTAGE = 1.0; // = 100% play time
    const DEFAULT_PARTIAL_UPDATE_PERCENTAGE = 1.0; // = receive 100% update

    private $_Id;
    private $_PartialPlayPercentage;
    private $_PartialUpdatePercentage;

    /**
     * Constructs a player.
     * 
     * @param mixed $id The identifier for the player, such as a name.
     * @param number $partialPlayPercentage The weight percentage to give this player when calculating a new rank.
     * @param number $partialUpdatePercentage Indicated how much of a skill update a player should receive where 0 represents no update and 1.0 represents 100% of the update.
     */
    public function __construct($id,
                                $partialPlayPercentage = self::DEFAULT_PARTIAL_PLAY_PERCENTAGE,
                                $partialUpdatePercentage = self::DEFAULT_PARTIAL_UPDATE_PERCENTAGE)
    {
        // If they don't want to give a player an id, that's ok...
        Guard::argumentInRangeInclusive($partialPlayPercentage, 0.0, 1.0, "partialPlayPercentage");
        Guard::argumentInRangeInclusive($partialUpdatePercentage, 0, 1.0, "partialUpdatePercentage");
        $this->_Id = $id;
        $this->_PartialPlayPercentage = $partialPlayPercentage;
        $this->_PartialUpdatePercentage = $partialUpdatePercentage;
    }

    /**
     * The identifier for the player, such as a name.
     */
    public function getId()
    {
        $id = $this->_Id;
        return $this->_Id;
    }
    
    /**
     * Indicates the percent of the time the player should be weighted where 0.0 indicates the player didn't play and 1.0 indicates the player played 100% of the time.
     */
    public function getPartialPlayPercentage()
    {
        return $this->_PartialPlayPercentage;
    }
    
    /**
     * Indicated how much of a skill update a player should receive where 0.0 represents no update and 1.0 represents 100% of the update.
     */
    public function getPartialUpdatePercentage()
    {
        return $this->_PartialUpdatePercentage;
    }
    
    public function __toString()
    {
        if ($this->_Id != null)
        {
            return (string)$this->_Id;
        }

        return parent::__toString();
    }
}
?>
