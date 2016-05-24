<?php namespace Moserware\Skills;

class RatingContainer
{
    private $_playerToRating;

    public function __construct()
    {
        $this->_playerToRating = new HashMap();
    }

    public function getRating(Player $player)
    {
        return $this->_playerToRating->getValue($player);
    }

    public function setRating(Player $player, Rating $rating)
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
        return $this->_playerToRating->count();
    }
}
