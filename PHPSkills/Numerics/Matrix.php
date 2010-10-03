<?php
namespace Moserware\Numerics;

class Matrix
{
    const ERROR_TOLERANCE = 0.0000000001;

    private $_matrixRowData;
    private $_rowCount;
    private $_columnCount;

    public function __construct($rows = 0, $columns = 0, $matrixData = null)
    {
        $this->_rowCount = $rows;
        $this->_columnCount = $columns;
        $this->_matrixRowData = $matrixData;
    }

    public static function fromColumnValues($rows, $columns, $columnValues)
    {
        $data = array();
        $result = new Matrix($rows, $columns, $data);

        for($currentColumn = 0; $currentColumn < $columns; $currentColumn++)
        {
            $currentColumnData = $columnValues[$currentColumn];

            for($currentRow = 0; $currentRow < $rows; $currentRow++)
            {
                $result->setValue($currentRow, $currentColumn, $currentColumnData[$currentRow]);
            }
        }

        return $result;
    }

    public static function fromRowsColumns()
    {
        $args = \func_get_args();
        $rows = $args[0];
        $cols = $args[1];
        $result = new Matrix($rows, $cols);
        $currentIndex = 2;

        for($currentRow = 0; $currentRow < $rows; $currentRow++)
        {
            for($currentCol = 0; $currentCol < $cols; $currentCol++)
            {
                $result->setValue($currentRow, $currentCol, $args[$currentIndex++]);
            }
        }

        return $result;
    }

    public function getRowCount()
    {
        return $this->_rowCount;
    }

    public function getColumnCount()
    {
        return $this->_columnCount;
    }

    public function getValue($row, $col)
    {
        return $this->_matrixRowData[$row][$col];
    }

    public function setValue($row, $col, $value)
    {
        $this->_matrixRowData[$row][$col] = $value;
    }

    public function getTranspose()
    {
        // Just flip everything
        $transposeMatrix = array();

        $rowMatrixData = $this->_matrixRowData;
        for ($currentRowTransposeMatrix = 0;
             $currentRowTransposeMatrix < $this->_columnCount;
             $currentRowTransposeMatrix++)
        {
            for ($currentColumnTransposeMatrix = 0;
                 $currentColumnTransposeMatrix < $this->_rowCount;
                 $currentColumnTransposeMatrix++)
            {
                $transposeMatrix[$currentRowTransposeMatrix][$currentColumnTransposeMatrix] =
                    $rowMatrixData[$currentColumnTransposeMatrix][$currentRowTransposeMatrix];
            }
        }

        return new Matrix($this->_columnCount, $this->_rowCount, $transposeMatrix);
    }

    private function isSquare()
    {
        return ($this->_rowCount == $this->_columnCount) && ($this->_rowCount > 0);
    }

    public function getDeterminant()
    {
        // Basic argument checking
        if (!$this->isSquare())
        {
            throw new Exception("Matrix must be square!");
        }

        if ($this->_rowCount == 1)
        {
            // Really happy path :)
            return $this->_matrixRowData[0][0];
        }

        if ($this->_rowCount == 2)
        {
            // Happy path!
            // Given:
            // | a b |
            // | c d |
            // The determinant is ad - bc
            $a = $this->_matrixRowData[0][0];
            $b = $this->_matrixRowData[0][1];
            $c = $this->_matrixRowData[1][0];
            $d = $this->_matrixRowData[1][1];
            return $a*$d - $b*$c;
        }

        // I use the Laplace expansion here since it's straightforward to implement.
        // It's O(n^2) and my implementation is especially poor performing, but the
        // core idea is there. Perhaps I should replace it with a better algorithm
        // later.
        // See http://en.wikipedia.org/wiki/Laplace_expansion for details

        $result = 0.0;

        // I expand along the first row
        for ($currentColumn = 0; $currentColumn < $this->_columnCount; $currentColumn++)
        {
            $firstRowColValue = $this->_matrixRowData[0][$currentColumn];
            $cofactor = $this->getCofactor(0, $currentColumn);
            $itemToAdd = $firstRowColValue*$cofactor;
            $result = $result + $itemToAdd;
        }

        return $result;
    }

    public function getAdjugate()
    {
        if (!$this->isSquare())
        {
            throw new Exception("Matrix must be square!");
        }

        // See http://en.wikipedia.org/wiki/Adjugate_matrix
        if ($this->_rowCount == 2)
        {
            // Happy path!
            // Adjugate of:
            // | a b |
            // | c d |
            // is
            // | d -b |
            // | -c a |

            $a = $this->_matrixRowData[0][0];
            $b = $this->_matrixRowData[0][1];
            $c = $this->_matrixRowData[1][0];
            $d = $this->_matrixRowData[1][1];

            return new SquareMatrix( $d, -$b,
                                    -$c,  $a);
        }

        // The idea is that it's the transpose of the cofactors
        $result = array();

        for ($currentColumn = 0; $currentColumn < $this->_columnCount; $currentColumn++)
        {
            for ($currentRow = 0; $currentRow < $this->_rowCount; $currentRow++)
            {
                $result[$currentColumn][$currentRow] = $this->getCofactor($currentRow, $currentColumn);
            }
        }

        return new Matrix($this->_columnCount, $this->_rowCount, $result);
    }

    public function getInverse()
    {
        if (($this->_rowCount == 1) && ($this->_columnCount == 1))
        {
            return new SquareMatrix(1.0/$this->_matrixRowData[0][0]);
        }

        // Take the simple approach:
        // http://en.wikipedia.org/wiki/Cramer%27s_rule#Finding_inverse_matrix
        $determinantInverse = 1.0 / $this->getDeterminant();
        $adjugate = $this->getAdjugate();

        return self::scalarMultiply($determinantInverse, $adjugate);
    }

    public static function scalarMultiply($scalarValue, $matrix)
    {
        $rows = $matrix->getRowCount();
        $columns = $matrix->getColumnCount();
        $newValues = array();

        for ($currentRow = 0; $currentRow < $rows; $currentRow++)
        {
            for ($currentColumn = 0; $currentColumn < $columns; $currentColumn++)
            {
                $newValues[$currentRow][$currentColumn] = $scalarValue*$matrix->getValue($currentRow, $currentColumn);
            }
        }

        return new Matrix($rows, $columns, $newValues);
    }

    public static function add($left, $right)
    {
        if (
                ($left->getRowCount() != $right->getRowCount())
                ||
                ($left->getColumnCount() != $right->getColumnCount())
           )
        {
            throw new Exception("Matrices must be of the same size");
        }

        // simple addition of each item

        $resultMatrix = array();

        for ($currentRow = 0; $currentRow < $left->getRowCount(); $currentRow++)
        {
            for ($currentColumn = 0; $currentColumn < $right->getColumnCount(); $currentColumn++)
            {
                $resultMatrix[$currentRow][$currentColumn] =
                   $left->getValue($currentRow, $currentColumn)
                   +
                   $right->getValue($currentRow, $currentColumn);
            }
        }

        return new Matrix($left->getRowCount(), $right->getColumnCount(), $resultMatrix);
    }

    public static function multiply($left, $right)
    {
        // Just your standard matrix multiplication.
        // See http://en.wikipedia.org/wiki/Matrix_multiplication for details

        if ($left->getColumnCount() != $right->getRowCount())
        {
            throw new Exception("The width of the left matrix must match the height of the right matrix");
        }

        $resultRows = $left->getRowCount();
        $resultColumns = $right->getColumnCount();

        $resultMatrix = array();

        for ($currentRow = 0; $currentRow < $resultRows; $currentRow++)
        {            
            for ($currentColumn = 0; $currentColumn < $resultColumns; $currentColumn++)
            {
                $productValue = 0;

                for ($vectorIndex = 0; $vectorIndex < $left->getColumnCount(); $vectorIndex++)
                {
                    $leftValue = $left->getValue($currentRow, $vectorIndex);
                    $rightValue = $right->getValue($vectorIndex, $currentColumn);
                    $vectorIndexProduct = $leftValue*$rightValue;
                    $productValue = $productValue + $vectorIndexProduct;
                }

                $resultMatrix[$currentRow][$currentColumn] = $productValue;
            }
        }

        return new Matrix($resultRows, $resultColumns, $resultMatrix);
    }   

    private function getMinorMatrix($rowToRemove, $columnToRemove)
    {
        // See http://en.wikipedia.org/wiki/Minor_(linear_algebra)

        // I'm going to use a horribly naïve algorithm... because I can :)
        $result = array();

        $actualRow = 0;

        for ($currentRow = 0; $currentRow < $this->_rowCount; $currentRow++)
        {
            if ($currentRow == $rowToRemove)
            {
                continue;
            }

            $actualCol = 0;

            for ($currentColumn = 0; $currentColumn < $this->_columnCount; $currentColumn++)
            {
                if ($currentColumn == $columnToRemove)
                {
                    continue;
                }

                $result[$actualRow][$actualCol] = $this->_matrixRowData[$currentRow][$currentColumn];

                $actualCol++;
            }

            $actualRow++;
        }

        return new Matrix($this->_rowCount - 1, $this->_columnCount - 1, $result);
    }

    public function getCofactor($rowToRemove, $columnToRemove)
    {
        // See http://en.wikipedia.org/wiki/Cofactor_(linear_algebra) for details
        // REVIEW: should things be reversed since I'm 0 indexed?
        $sum = $rowToRemove + $columnToRemove;
        $isEven = ($sum%2 == 0);

        if ($isEven)
        {
            return $this->getMinorMatrix($rowToRemove, $columnToRemove)->getDeterminant();
        }
        else
        {
            return -1.0*$this->getMinorMatrix($rowToRemove, $columnToRemove)->getDeterminant();
        }
    }

    public function equals($otherMatrix)
    {
        // If one is null, but not both, return false.
        if ($otherMatrix == null)
        {
            return false;
        }

        if (($this->_rowCount != $otherMatrix->getRowCount()) || ($this->_columnCount != $otherMatrix->getColumnCount()))
        {
            return false;
        }

        for ($currentRow = 0; $currentRow < $this->_rowCount; $currentRow++)
        {
            for ($currentColumn = 0; $currentColumn < $this->_columnCount; $currentColumn++)
            {
                $delta =
                    abs($this->_matrixRowData[$currentRow][$currentColumn] -
                        $otherMatrix->getValue($currentRow, $currentColumn));

                if ($delta > self::ERROR_TOLERANCE)
                {
                    return false;
                }
            }
        }

        return true;
    }
}

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

class IdentityMatrix extends DiagonalMatrix
{
    public function __construct($rows)
    {
        parent::__construct(\array_fill(0, $rows, 1));
    }
}
?>
