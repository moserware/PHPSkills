<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . "/Guard.php");
require_once(dirname(__FILE__) . "/ISupportPartialPlay.php");
require_once(dirname(__FILE__) . "/ISupportPartialUpdate.php");

/// <summary>
/// Represents a player who has a <see cref="Rating"/>.
/// </summary>
class Player implements ISupportPartialPlay, ISupportPartialUpdate
{
    const DEFAULT_PARTIAL_PLAY_PERCENTAGE = 1.0; // = 100% play time
    const DEFAULT_PARTIAL_UPDATE_PERCENTAGE = 1.0; // = receive 100% update

    private $_Id;
    private $_PartialPlayPercentage;
    private $_PartialUpdatePercentage;

    /// <summary>
    /// Constructs a player.
    /// </summary>
    /// <param name="id">The identifier for the player, such as a name.</param>
    /// <param name="partialPlayPercentage">The weight percentage to give this player when calculating a new rank.</param>
    /// <param name="partialUpdatePercentage">/// Indicated how much of a skill update a player should receive where 0 represents no update and 1.0 represents 100% of the update.</param>
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

    /// <summary>
    /// The identifier for the player, such as a name.
    /// </summary>
    public function getId()
    {
        return $this->_Id;
    }

    #region ISupportPartialPlay Members

    /// <summary>
    /// Indicates the percent of the time the player should be weighted where 0.0 indicates the player didn't play and 1.0 indicates the player played 100% of the time.
    /// </summary>
    public function getPartialPlayPercentage()
    {
        return $this->_PartialPlayPercentage;
    }

    #endregion

    #region ISupportPartialUpdate Members

    /// <summary>
    /// Indicated how much of a skill update a player should receive where 0.0 represents no update and 1.0 represents 100% of the update.
    /// </summary>
    public function getPartialUpdatePercentage()
    {
        return $this->_PartialUpdatePercentage;
    }

    #endregion

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
