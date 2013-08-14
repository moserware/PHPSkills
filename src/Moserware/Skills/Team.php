<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . '/Player.php');
require_once(dirname(__FILE__) . '/Rating.php');
require_once(dirname(__FILE__) . '/RatingContainer.php');

class Team extends RatingContainer
{
    public function __construct(Player $player = null, Rating $rating = null)
    {
        parent::__construct();
        
        if(!\is_null($player))
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

?>
