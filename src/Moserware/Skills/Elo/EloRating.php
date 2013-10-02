<?php
namespace Moserware\Skills\Elo;

use Moserware\Skills\Rating;

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