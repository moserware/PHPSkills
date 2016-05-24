<?php namespace Moserware\Skills\Numerics;

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