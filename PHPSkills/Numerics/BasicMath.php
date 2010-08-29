<?php
/**
 * Basic math functions.
 *
 * PHP version 5
 *
 * @category   Math
 * @package    PHPSkills
 * @author     Jeff Moser <jeff@moserware.com>
 * @copyright  2010 Jeff Moser 
 */

function square($x)
{
    return $x * $x;
}

function sum($itemsToSum, $funcName )
{
    $mappedItems = array_map($funcName, $itemsToSum);
    return array_sum($mappedItems);
}

?>