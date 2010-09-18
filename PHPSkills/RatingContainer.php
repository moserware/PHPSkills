<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . "/HashMap.php");

class RatingContainer
{
    private $_playerToRating;

    public function __construct()
    {
        $this->_playerToRating = new \HashMap();
    }

    public function getRating($player)
    {
        return $this->_playerToRating->getValue($player);
    }

    public function setRating($player, $rating)
    {
        return $this->_playerToRating->setValue($player, $rating);
    }
    
    public function getAllPlayers()
    {
        return $this->_playerToRating->getAllKeys();
    }
    
    public function getAllRatings()
    {
        return $this->_playerToRating->getAllValues();
    }

    public function count()
    {
        return \count($this->_playerToRating->count());
    }    
}
?>
