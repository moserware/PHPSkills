<?php

namespace Moserware\Skills;

/**
 * Helper class to sort ranks in non-decreasing order.
 */
class RankSorter
{
    /**
     * Performs an in-place sort of the items in according to the ranks in non-decreasing order.
     * 
     * @param $items The items to sort according to the order specified by ranks.
     * @param $ranks The ranks for each item where 1 is first place.
     */
    public static function sort(array $teams, array $teamRanks)
    {        
        array_multisort($teamRanks, $teams);
        return $teams;
    }
}

?>