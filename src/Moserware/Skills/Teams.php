<?php
namespace Moserware\Skills;

class Teams
{
    public static function concat(/*variable arguments*/)
    {
        $args = \func_get_args();
        $result = array();

        foreach ($args as $currentTeam) {
            $localCurrentTeam = $currentTeam;
            $result[] = $localCurrentTeam;
        }

        return $result;
    }
}
?>
