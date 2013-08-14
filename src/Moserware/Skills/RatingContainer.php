<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . "/HashMap.php");
require_once(dirname(__FILE__) . "/Player.php");
require_once(dirname(__FILE__) . "/Rating.php");

class RatingContainer
{
    private $_playerToRating;

    public function __construct()
    {
        $this->_playerToRating = new HashMap();
    }

    public function getRating(Player $player)
    {
        $rating = $this->_playerToRating->getValue($player);
        return $rating;
    }

    public function setRating(Player $player, Rating $rating)
    {
        return $this->_playerToRating->setValue($player, $rating);
    }
    
    public function getAllPlayers()
    {
        $allPlayers = $this->_playerToRating->getAllKeys();
        return $allPlayers;
    }
    
    public function getAllRatings()
    {
        $allRatings = $this->_playerToRating->getAllValues();
        return $allRatings;
    }

    public function count()
    {
        return $this->_playerToRating->count();
    }    
}
?>
