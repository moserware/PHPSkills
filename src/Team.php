<?php namespace Moserware\Skills;

class Team extends RatingContainer
{
    public function __construct(Player $player = null, Rating $rating = null)
    {
        parent::__construct();
        
        if(!is_null($player))
        {
            $this->addPlayer($player, $rating);
        }
    }

    public function addPlayer(Player $player, Rating $rating)
    {
        $this->setRating($player, $rating);
        return $this;
    }    
}