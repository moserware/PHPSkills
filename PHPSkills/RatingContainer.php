<?php
namespace Moserware\Skills;

class RatingContainer
{
    private $_playerHashToRating = array();
    private $_playerHashToPlayer = array();

    public function getRating($player)
    {
        return $this->_playerHashToRating[self::getHash($player)];
    }

    public function setRating($player, $rating)
    {
        $hash = self::getHash($player);
        $this->_playerHashToPlayer[$hash] = $player;
        $this->_playerHashToRating[$hash] = $rating;
        return $this;
    }
    
    public function getAllPlayers()
    {
        return \array_values($this->_playerHashToPlayer);
    }
    
    public function getAllRatings()
    {
        return \array_values($this->_playerHashToRating);
    }

    public function count()
    {
        return \count($this->_playerHashToPlayer);
    }
    private static function getHash($player)
    {
        if(\is_object($player))
        {
            return \spl_object_hash($player);
        }

        return $player;
    }
}
?>
