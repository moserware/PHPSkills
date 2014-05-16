<?php
namespace Moserware\Skills\Numerics\Matrix;

use Moserware\Skills\Numerics\Matrix;

class SquareMatrix extends Matrix
{
    public function __construct()
    {
        $allValues = \func_get_args();
        $rows = (int) sqrt(count($allValues));
        $cols = $rows;

        $matrixData = array();
        $allValuesIndex = 0;

        for ($currentRow = 0; $currentRow < $rows; $currentRow++)
        {
            for ($currentColumn = 0; $currentColumn < $cols; $currentColumn++)
            {
                $matrixData[$currentRow][$currentColumn] = $allValues[$allValuesIndex++];
            }
        }

        parent::__construct($rows, $cols, $matrixData);
    }
}