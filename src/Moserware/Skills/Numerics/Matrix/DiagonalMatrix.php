<?php
namespace Moserware\Skills\Numerics\Matrix;

use Moserware\Skills\Numerics\Matrix;

class DiagonalMatrix extends Matrix
{
    public function __construct(array $diagonalValues)
    {
        $diagonalCount = count($diagonalValues);
        $rowCount = $diagonalCount;
        $colCount = $rowCount;

        parent::__construct($rowCount, $colCount);

        for($currentRow = 0; $currentRow < $rowCount; $currentRow++)
        {
            for($currentCol = 0; $currentCol < $colCount; $currentCol++)
            {
                if($currentRow == $currentCol)
                {
                    $this->setValue($currentRow, $currentCol, $diagonalValues[$currentRow]);
                }
                else
                {
                    $this->setValue($currentRow, $currentCol, 0);
                }
            }
        }
    }
}