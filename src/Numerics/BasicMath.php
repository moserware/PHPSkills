<?php
/**
 * Basic math functions.
 * 
 * @author     Jeff Moser <jeff@moserware.com>
 * @copyright  2010 Jeff Moser 
 */

/**
 * Squares the input (x^2 = x * x)
 * @param number $x Value to square (x)
 * @return number The squared value (x^2)
 */
function square($x)
{
    return $x * $x;
}

/**
 * Sums the items in $itemsToSum
 * @param array $itemsToSum The items to sum,
 * @param callback $callback The function to apply to each array element before summing.
 * @return number The sum.
 */
function sum(array $itemsToSum, $callback )
{
    $mappedItems = array_map($callback, $itemsToSum);
    return array_sum($mappedItems);
}

?>