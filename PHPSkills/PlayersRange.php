<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . "/Numerics/Range.php");

use Moserware\Numerics\Range;

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