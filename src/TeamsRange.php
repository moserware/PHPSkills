<?php
namespace Skills;

use Skills\Numerics\Range;

class TeamsRange extends Range
{
    public function __construct($min, $max)
    {
        parent::__construct($min, $max);
    }

    protected static function create($min, $max)
    {
        return new TeamsRange($min, $max);
    }
}

?>
