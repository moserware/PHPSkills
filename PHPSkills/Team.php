<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . '/RatingContainer.php');

class Team extends RatingContainer
{
    public function __construct(&$player = null, &$rating = null)
    {
        parent::__construct();
        
        if(!\is_null($player))
        {
            $this->addPlayer($player, $rating);
        }
    }

    public function addPlayer(&$player, &$rating)
    {
        $this->setRating($player, $rating);
        return $this;
    }
    
}

?>
