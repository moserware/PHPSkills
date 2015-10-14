<?php
namespace Skills\Elo;

use Skills\Rating;

/**
 * An Elo rating represented by a single number (mean).
 */
class EloRating extends Rating
{
    public function __construct($rating)        
    {
        parent::__construct($rating, 0);
    }
}

?>
