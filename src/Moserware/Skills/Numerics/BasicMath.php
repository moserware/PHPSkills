<?php
/**
 * Basic math functions.
 * 
 * @author     Jeff Moser <jeff@moserware.com>
 * @copyright  2010 Jeff Moser 
 */

namespace Moserware\Skills\Numerics;

class BasicMath
{
    /**
     * Squares the input (x^2 = x * x)
     *
     * @deprecated Use PHP internal method pow() instead.
     *
     * @param number $x Value to square (x)
     * @return number The squared value (x^2)
     */
    public static function square($x)
    {
        return $x * $x;
    }

    /**
     * Sums the items in $itemsToSum
     *
     * @param array $itemsToSum The items to sum,
     * @param callback $callback The function to apply to each array element before summing.
     * @return number The sum.
     */
    public static function sum(array $itemsToSum, $callback )
    {
        $mappedItems = array_map($callback, $itemsToSum);
        return array_sum($mappedItems);
    }
}
?>