<?php

namespace Moserware\Skills;

/// <summary>
/// Helper class to sort ranks in non-decreasing order.
/// </summary>
class RankSorter
{
    /// <summary>
    /// Performs an in-place sort of the <paramref name="items"/> in according to the <paramref name="ranks"/> in non-decreasing order.
    /// </summary>
    /// <typeparam name="T">The types of items to sort.</typeparam>
    /// <param name="items">The items to sort according to the order specified by <paramref name="ranks"/>.</param>
    /// <param name="ranks">The ranks for each item where 1 is first place.</param>
    public static function sort(array &$teams, array &$teamRanks)
    {        
        array_multisort($teamRanks, $teams);
        return $teams;
    }
}

?>