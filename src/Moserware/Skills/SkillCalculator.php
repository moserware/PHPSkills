<?php
namespace Moserware\Skills;

require_once(dirname(__FILE__) . "/GameInfo.php");
require_once(dirname(__FILE__) . "/PlayersRange.php");
require_once(dirname(__FILE__) . "/TeamsRange.php");

/** 
 * Base class for all skill calculator implementations.
 */
abstract class SkillCalculator
{
    private $_supportedOptions;
    private $_playersPerTeamAllowed;
    private $_totalTeamsAllowed;
    
    protected function __construct($supportedOptions, TeamsRange $totalTeamsAllowed, PlayersRange $playerPerTeamAllowed)
    {
        $this->_supportedOptions = $supportedOptions;
        $this->_totalTeamsAllowed = $totalTeamsAllowed;
        $this->_playersPerTeamAllowed = $playerPerTeamAllowed;
    }

    /**
     * Calculates new ratings based on the prior ratings and team ranks.
     * @param $gameInfo Parameters for the game.
     * @param $teams A mapping of team players and their ratings.
     * @param $teamRanks The ranks of the teams where 1 is first place. For a tie, repeat the number (e.g. 1, 2, 2).
     * @return All the players and their new ratings.
     */
    public abstract function calculateNewRatings(GameInfo $gameInfo,
                                                 array $teamsOfPlayerToRatings,
                                                 array $teamRanks);

    /**
     * Calculates the match quality as the likelihood of all teams drawing.
     * 
     * @param $gameInfo Parameters for the game.
     * @param $teams A mapping of team players and their ratings.
     * @return The quality of the match between the teams as a percentage (0% = bad, 100% = well matched).
     */
    public abstract function calculateMatchQuality(GameInfo $gameInfo,
                                                   array $teamsOfPlayerToRatings);

    public function isSupported($option)
    {           
        return ($this->_supportedOptions & $option) == $option;             
    }    

    protected function validateTeamCountAndPlayersCountPerTeam(array $teamsOfPlayerToRatings)
    {
        self::validateTeamCountAndPlayersCountPerTeamWithRanges($teamsOfPlayerToRatings, $this->_totalTeamsAllowed, $this->_playersPerTeamAllowed);
    }

    private static function validateTeamCountAndPlayersCountPerTeamWithRanges(
        array $teams,
        TeamsRange $totalTeams,
        PlayersRange $playersPerTeam)
    {        
        $countOfTeams = 0;
        
        foreach ($teams as $currentTeam)
        {
            if (!$playersPerTeam->isInRange($currentTeam->count()))
            {
                throw new \Exception("Player count is not in range");
            }
            $countOfTeams++;
        }

        if (!$totalTeams->isInRange($countOfTeams))
        {
            throw new \Exception("Team range is not in range");
        }
    }
}

class SkillCalculatorSupportedOptions
{
    const NONE = 0x00;
    const PARTIAL_PLAY = 0x01;
    const PARTIAL_UPDATE = 0x02;
}
?>
