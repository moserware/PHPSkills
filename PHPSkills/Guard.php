<?php
namespace Moserware\Skills;

/// <summary>
/// Verifies argument contracts.
/// </summary>
/// <remarks>These are used until .NET 4.0 ships with Contracts. For more information,
/// see http://www.moserware.com/2008/01/borrowing-ideas-from-3-interesting.html</remarks>
class Guard
{
    public static function argumentNotNull($value, $parameterName)
    {
        if ($value == null)
        {
            throw new Exception($parameterName . " can not be null");
        }
    }

    public static function argumentIsValidIndex($index, $count, $parameterName)
    {
        if (($index < 0) || ($index >= $count))
        {
            throw new Exception($parameterName . " is an invalid index");
        }
    }

    public static function argumentInRangeInclusive($value, $min, $max, $parameterName)
    {
        if (($value < $min) || ($value > $max))
        {
            throw new Exception($parameterName . " is not in the valid range [" . $min . ", " . $max . "]");
        }
    }
}
?>
