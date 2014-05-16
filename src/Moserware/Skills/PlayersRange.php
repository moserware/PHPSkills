<?php
namespace Moserware\Skills;

use Moserware\Skills\Numerics\Range;

class PlayersRange extends Range
{
    public function __construct($min, $max)
    {
        parent::__construct($min, $max);
    }
    
    protected static function create($min, $max)
    {
        return new PlayersRange($min, $max);
    }
}

?>