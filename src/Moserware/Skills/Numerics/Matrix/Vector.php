<?php
namespace Moserware\Skills\Numerics\Matrix;

use Moserware\Skills\Numerics\Matrix;

class Vector extends Matrix
{
    public function __construct(array $vectorValues)
    {
        $columnValues = array();
        foreach($vectorValues as $currentVectorValue)
        {
            $columnValues[] = array($currentVectorValue);
        }
        parent::__construct(count($vectorValues), 1, $columnValues);
    }
}