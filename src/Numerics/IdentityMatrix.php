<?php namespace Moserware\Skills\Numerics;

class IdentityMatrix extends DiagonalMatrix
{
    public function __construct($rows)
    {
        parent::__construct(array_fill(0, $rows, 1));
    }
}